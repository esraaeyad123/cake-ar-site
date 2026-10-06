/**
 * يركّب الكيكة من النموذج الأساسي (البورد + اللوجو + أبعاد الأدوار) + الإضافات المختارة،
 * ويرجع ملف glb واحد (blob URL) يُعرض في model-viewer.
 *
 * - جسم الكيكة يُعاد بناؤه بحواف دائرية ولون حقيقي (بدل الأسطوانة الحادة بلون vertex).
 * - اللوجو (شبكة ألوان) يتحول لصورة texture حتى يظهر في الواقع المعزز على الآيفون
 *   (model-viewer يولّد ملف usdz من المشهد المعروض).
 * - كل الأبعاد بالمتر، وتُقرأ من النموذج نفسه فتشتغل الإضافات على كل الأحجام والأدوار.
 */
import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { GLTFExporter } from 'three/addons/exporters/GLTFExporter.js';
import { mergeGeometries } from 'three/addons/utils/BufferGeometryUtils.js';

const loader = new GLTFLoader();
const exporter = new GLTFExporter();
const baseCache = new Map();

// ---------- النموذج الأساسي ----------

function loadBase(url) {
    if (!baseCache.has(url)) {
        baseCache.set(url, loader.loadAsync(url).then(extractBase).catch(e => {
            baseCache.delete(url);
            throw e;
        }));
    }
    return baseCache.get(url);
}

function extractBase(gltf) {
    const tiers = [];
    let board = null;
    let logo = null;

    gltf.scene.updateMatrixWorld(true);
    gltf.scene.traverse(obj => {
        if (!obj.isMesh) return;
        const geo = obj.geometry.clone().applyMatrix4(obj.matrixWorld);
        const name = obj.material.name || '';
        geo.computeBoundingBox();
        const box = geo.boundingBox;

        if (name.startsWith('CakeMaterial')) {
            tiers.push({ r: (box.max.x - box.min.x) / 2, y0: box.min.y, y1: box.max.y });
        } else if (name.startsWith('Board')) {
            board = { geo, box };
        } else if (name.startsWith('Logo')) {
            logo = logoFromGrid(geo, box);
        }
    });

    tiers.sort((a, b) => a.y0 - b.y0);
    return { tiers, board, logo };
}

// اللوجو مخزن كشبكة مربعات ملونة (vertex colors) — نرسمها في canvas ونلصقها على مستطيل واحد
function logoFromGrid(geo, box) {
    const pos = geo.attributes.position;
    const col = geo.attributes.color;
    if (!col) return null;

    const xs = new Set(), zs = new Set();
    for (let i = 0; i < pos.count; i++) {
        xs.add(pos.getX(i).toFixed(5));
        zs.add(pos.getZ(i).toFixed(5));
    }
    const cols = xs.size - 1, rows = zs.size - 1;
    const w = box.max.x - box.min.x, d = box.max.z - box.min.z;

    const canvas = document.createElement('canvas');
    canvas.width = cols;
    canvas.height = rows;
    const ctx = canvas.getContext('2d');
    const img = ctx.createImageData(cols, rows);
    const c = new THREE.Color();

    // كل مربع = 4 رؤوس بنفس اللون، نأخذ مركز المربع لتحديد البكسل
    for (let q = 0; q + 3 < pos.count; q += 4) {
        let cx = 0, cz = 0;
        for (let k = 0; k < 4; k++) { cx += pos.getX(q + k); cz += pos.getZ(q + k); }
        const px = Math.min(cols - 1, Math.floor(((cx / 4) - box.min.x) / w * cols));
        const py = Math.min(rows - 1, Math.floor(((cz / 4) - box.min.z) / d * rows));
        c.setRGB(col.getX(q), col.getY(q), col.getZ(q), THREE.LinearSRGBColorSpace);
        const [r, g, b] = c.getStyle(THREE.SRGBColorSpace).match(/\d+/g).map(Number);
        const i = (py * cols + px) * 4;
        img.data[i] = r; img.data[i + 1] = g; img.data[i + 2] = b; img.data[i + 3] = 255;
    }
    ctx.putImageData(img, 0, 0);

    return { canvas, box };
}

// ---------- أدوات ----------

function rng(seed) {
    let s = seed >>> 0;
    return () => ((s = (s * 1664525 + 1013904223) >>> 0) / 4294967296);
}

function mat(name, color, roughness, metalness = 0) {
    return new THREE.MeshStandardMaterial({ name, color: new THREE.Color(color), roughness, metalness });
}

function addMerged(group, name, geos, material) {
    if (!geos.length) return;
    const mesh = new THREE.Mesh(mergeGeometries(geos), material);
    mesh.name = name;
    group.add(mesh);
}

// ---------- جسم الكيكة ----------

function cakeBody(tier, material) {
    const { r, y0, y1 } = tier;
    const e = Math.min(0.009, (y1 - y0) * 0.14, r * 0.12);
    const pts = [new THREE.Vector2(0, y0), new THREE.Vector2(r, y0), new THREE.Vector2(r, y1 - e)];
    for (let i = 1; i <= 10; i++) {
        const a = (i / 10) * Math.PI / 2;
        pts.push(new THREE.Vector2(r - e + Math.cos(a) * e, y1 - e + Math.sin(a) * e));
    }
    pts.push(new THREE.Vector2(0, y1));
    const mesh = new THREE.Mesh(new THREE.LatheGeometry(pts, 96), material);
    mesh.name = 'CakeTier';
    return mesh;
}

// ---------- الإضافات ----------

const TOPPINGS = {
    // لؤلؤ متناثر على جوانب كل دور (أكثف قرب الأعلى) + حبات على حافة السطح
    pearls(tiers, group, seed) {
        const rand = rng(seed);
        const geos = [];
        tiers.forEach(t => {
            const h = t.y1 - t.y0;
            const side = Math.round(2 * Math.PI * t.r * h * 6000);
            for (let i = 0; i < side; i++) {
                const s = 0.0022 + rand() * 0.0024;
                const a = rand() * Math.PI * 2;
                const y = t.y0 + h * (1 - Math.pow(rand(), 1.8)) * 0.9 + s;
                const g = new THREE.SphereGeometry(s, 8, 6);
                g.translate(Math.cos(a) * (t.r + s * 0.35), Math.min(y, t.y1 - s), Math.sin(a) * (t.r + s * 0.35));
                geos.push(g);
            }
            const ring = Math.round(2 * Math.PI * t.r * 380);
            for (let i = 0; i < ring; i++) {
                const s = 0.0024 + rand() * 0.002;
                const a = rand() * Math.PI * 2;
                const rr = t.r - 0.006 - rand() * 0.012;
                const g = new THREE.SphereGeometry(s, 8, 6);
                g.translate(Math.cos(a) * rr, t.y1 + s * 0.6, Math.sin(a) * rr);
                geos.push(g);
            }
        });
        addMerged(group, 'Pearls', geos, mat('PearlMaterial', '#FFFDF8', 0.25, 0.15));
    },

    // حواف كريمة (shell border) أسفل وأعلى كل دور
    piping(tiers, group) {
        const geos = [];
        const ring = (r, y, s) => {
            const n = Math.round((2 * Math.PI * r) / (s * 2.2));
            for (let i = 0; i < n; i++) {
                const a = (i / n) * Math.PI * 2;
                const g = new THREE.SphereGeometry(s, 12, 8);
                g.scale(1.6, 0.85, 1);
                g.rotateZ(0.35);
                g.rotateY(-(a + Math.PI / 2));
                g.translate(Math.cos(a) * r, y, Math.sin(a) * r);
                geos.push(g);
            }
        };
        tiers.forEach(t => {
            ring(t.r + 0.0015, t.y0 + 0.0045, 0.0055);
            ring(t.r - 0.0035, t.y1 + 0.001, 0.0042);
        });
        addMerged(group, 'Piping', geos, mat('CreamMaterial', '#FFF8EC', 0.75));
    },

    // حبات كريمة دائرية على حافة السطح (مثل كيكة Tony)
    beads(tiers, group) {
        const geos = [];
        tiers.forEach(t => {
            const s = Math.max(0.0042, Math.min(0.0058, t.r * 0.07));
            const r = t.r - s * 0.75;
            const n = Math.round((2 * Math.PI * r) / (s * 2.05));
            for (let i = 0; i < n; i++) {
                const a = (i / n) * Math.PI * 2;
                const g = new THREE.SphereGeometry(s, 12, 9);
                g.scale(1, 0.82, 1);
                g.translate(Math.cos(a) * r, t.y1 + s * 0.55, Math.sin(a) * r);
                geos.push(g);
            }
        });
        addMerged(group, 'Beads', geos, mat('CreamMaterial', '#FFFCF5', 0.7));
    },

    // كرز فوق دولوب كريمة على الدور العلوي
    cherries(tiers, group) {
        const t = tiers[tiers.length - 1];
        const n = t.r < 0.06 ? 3 : (t.r < 0.09 ? 5 : 7);
        const rc = 0.0085;
        const cherry = [], cream = [], stems = [];
        for (let i = 0; i < n; i++) {
            const a = (i / n) * Math.PI * 2 + 0.3;
            const x = Math.cos(a) * t.r * 0.62, z = Math.sin(a) * t.r * 0.62;
            const d = new THREE.SphereGeometry(0.0105, 16, 10);
            d.scale(1, 0.6, 1);
            d.translate(x, t.y1 + 0.004, z);
            cream.push(d);
            const c = new THREE.SphereGeometry(rc, 18, 14);
            c.translate(x, t.y1 + 0.009 + rc, z);
            cherry.push(c);
            const s = new THREE.CylinderGeometry(0.0007, 0.0009, 0.022, 6);
            s.translate(0, 0.011, 0);
            s.rotateZ(0.45);
            s.rotateY(-a);
            s.translate(x, t.y1 + 0.009 + rc * 1.8, z);
            stems.push(s);
        }
        addMerged(group, 'CherryCream', cream, mat('CreamMaterial', '#FFF8EC', 0.75));
        addMerged(group, 'Cherries', cherry, mat('CherryMaterial', '#A8001C', 0.18, 0.05));
        addMerged(group, 'Stems', stems, mat('StemMaterial', '#5B3A1E', 0.8));
    },

    // حبيبات ملونة على سطح الدور العلوي
    sprinkles(tiers, group, seed) {
        const t = tiers[tiers.length - 1];
        const rand = rng(seed + 7);
        const colors = ['#FF5C8A', '#FFD23F', '#3BCEAC', '#4D9DE0', '#F6F7EB', '#9B5DE5'];
        const byColor = colors.map(() => []);
        const n = Math.round(Math.PI * t.r * t.r * 70000);
        for (let i = 0; i < n; i++) {
            const a = rand() * Math.PI * 2;
            const rr = Math.sqrt(rand()) * (t.r - 0.01);
            const g = new THREE.CapsuleGeometry(0.0011, 0.0045, 2, 6);
            g.rotateZ(Math.PI / 2);
            g.rotateY(rand() * Math.PI);
            g.translate(Math.cos(a) * rr, t.y1 + 0.0011, Math.sin(a) * rr);
            byColor[Math.floor(rand() * colors.length)].push(g);
        }
        byColor.forEach((geos, i) =>
            addMerged(group, 'Sprinkles' + i, geos, mat('SprinkleMaterial' + i, colors[i], 0.45)));
    },
};

// ---------- صور على السطح (رسمة التصميم + الكتابة + الصورة المطبوعة) ----------

function decalMaterial(name, canvas) {
    const tex = new THREE.CanvasTexture(canvas);
    tex.colorSpace = THREE.SRGBColorSpace;
    tex.anisotropy = 4;
    return new THREE.MeshStandardMaterial({
        name, map: tex, transparent: true, depthWrite: false, roughness: 0.75,
        polygonOffset: true, polygonOffsetFactor: -2,
    });
}

// قرص على سطح الدور العلوي — الصورة المربعة تغطي قطر الكيكة كاملًا
function topDecal(tier, canvas) {
    const e = Math.min(0.009, (tier.y1 - tier.y0) * 0.14, tier.r * 0.12);
    const g = new THREE.CircleGeometry(tier.r - e * 0.5, 96);
    const pos = g.attributes.position, uv = g.attributes.uv;
    for (let i = 0; i < uv.count; i++) uv.setXY(i, 0.5 + pos.getX(i) / (2 * tier.r), 0.5 + pos.getY(i) / (2 * tier.r));
    g.rotateX(-Math.PI / 2);
    g.translate(0, tier.y1 + 0.0006, 0);
    const mesh = new THREE.Mesh(g, decalMaterial('TopDecalMaterial', canvas));
    mesh.name = 'TopDecal';
    return mesh;
}

// شريط على البورد أمام الكيكة للكتابة (يبدأ بعد اللوجو حتى لا يغطيه)
function boardStrip(base) {
    if (!base.board) return null;
    const box = base.board.box;
    const x0 = base.logo ? base.logo.box.max.x + 0.005 : box.min.x + 0.006;
    const x1 = box.max.x - 0.006;
    const z0 = base.tiers[0].r + 0.004, z1 = box.max.z - 0.004;
    if (z1 - z0 < 0.008 || x1 - x0 < 0.04) return null;
    return { x0, x1, z0, z1 };
}

function boardDecal(base, canvas) {
    const st = boardStrip(base);
    if (!st) return null;
    const g = new THREE.PlaneGeometry(st.x1 - st.x0, st.z1 - st.z0);
    g.rotateX(-Math.PI / 2);
    g.translate((st.x0 + st.x1) / 2, base.board.box.max.y + 0.0005, (st.z0 + st.z1) / 2);
    const mesh = new THREE.Mesh(g, decalMaterial('BoardTextMaterial', canvas));
    mesh.name = 'BoardText';
    return mesh;
}

/** نسبة عرض/ارتفاع شريط الكتابة على البورد، أو 0 إذا البورد ما فيه مساحة */
export async function boardTextAspect(baseUrl) {
    const st = boardStrip(await loadBase(baseUrl));
    return st ? (st.x1 - st.x0) / (st.z1 - st.z0) : 0;
}

// ---------- التركيب ----------

/**
 * @param {string} baseUrl  رابط glb الأساسي (الحجم + عدد الطبقات)
 * @param {{color: string, toppings: string[], topCanvas?: HTMLCanvasElement, boardCanvas?: HTMLCanvasElement}} options
 * @returns {Promise<string>} blob URL لملف glb المركّب
 */
export async function composeCake(baseUrl, { color, toppings = [], topCanvas = null, boardCanvas = null }) {
    const base = await loadBase(baseUrl);
    const root = new THREE.Group();
    root.name = 'JOMACAKE';

    if (base.board) {
        const g = base.board.geo.clone().toNonIndexed();
        g.deleteAttribute('color');
        g.computeVertexNormals();
        const board = new THREE.Mesh(g, mat('BoardMaterial', '#FFFFFF', 0.95));
        board.name = 'Board';
        root.add(board);
    }

    if (base.logo) {
        const { canvas, box } = base.logo;
        const tex = new THREE.CanvasTexture(canvas);
        tex.colorSpace = THREE.SRGBColorSpace;
        tex.flipY = false;
        const w = box.max.x - box.min.x, d = box.max.z - box.min.z;
        const plane = new THREE.PlaneGeometry(w, d);
        plane.rotateX(-Math.PI / 2);
        // بعد الدوران: v=0 عند z الأكبر — نقلبها حتى تطابق صفوف الـ canvas (z الأصغر = الصف الأول)
        const uv = plane.attributes.uv;
        for (let i = 0; i < uv.count; i++) uv.setY(i, 1 - uv.getY(i));
        plane.translate((box.min.x + box.max.x) / 2, box.max.y + 0.0002, (box.min.z + box.max.z) / 2);
        const logo = new THREE.Mesh(plane, new THREE.MeshStandardMaterial({ name: 'LogoMaterial', map: tex, roughness: 0.9 }));
        logo.name = 'Logo';
        root.add(logo);
    }

    const cakeMat = mat('CakeMaterial', color, 0.62);
    base.tiers.forEach(t => root.add(cakeBody(t, cakeMat)));

    if (topCanvas) root.add(topDecal(base.tiers[base.tiers.length - 1], topCanvas));
    const boardText = boardCanvas && boardDecal(base, boardCanvas);
    if (boardText) root.add(boardText);

    const seed = Math.round(base.tiers.reduce((a, t) => a + t.r * 1e4 + t.y1 * 1e3, 0));
    toppings.forEach(key => TOPPINGS[key]?.(base.tiers, root, seed));

    const glb = await exporter.parseAsync(root, { binary: true });
    return URL.createObjectURL(new Blob([glb], { type: 'model/gltf-binary' }));
}
