/**
 * يركّب كيكة صفحة المعاينة من النموذج الأساسي (البورد + اللوجو + أبعاد الأدوار)،
 * ويرجع ملف glb واحد (blob URL) يُعرض في model-viewer.
 *
 * - جسم الكيكة يُعاد بناؤه بحواف دائرية ولون كريمي (بدل الأسطوانة الحادة بلون vertex).
 * - اللوجو (شبكة ألوان) يتحول لصورة texture حتى يظهر في الواقع المعزز على الآيفون
 *   (model-viewer يولّد ملف usdz من المشهد المعروض).
 * - زخرفة الفنتج تُبنى لكل دور حسب أبعاده، فتشتغل على كل المقاسات والأدوار.
 * - كل الأبعاد بالمتر.
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

function mat(name, color, roughness, metalness = 0) {
    return new THREE.MeshStandardMaterial({ name, color: new THREE.Color(color), roughness, metalness });
}

// كريمة: إضاءة ذاتية خفيفة بنفس اللون حتى ما تطلع جوانب الكيكة رمادية/غامقة في المعاينة
function creamMat(name, color, glow) {
    const m = mat(name, color, 0.7);
    m.emissive = new THREE.Color(color);
    m.emissiveIntensity = glow;
    return m;
}

function addMerged(group, name, geos, material) {
    if (!geos.length) return;
    const mesh = new THREE.Mesh(mergeGeometries(geos), material);
    mesh.name = name;
    group.add(mesh);
}

// يوجّه شكلًا محليًا حول الكيكة: x على طول المحيط (المماس)، y للأعلى، z نحو المركز
function place(g, a, r, y) {
    g.rotateY(-(a + Math.PI / 2));
    g.translate(Math.cos(a) * r, y, Math.sin(a) * r);
    return g;
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

// ---------- زخرفة الفنتج ----------

/**
 * مثل كيكات الفنتج الكلاسيكية:
 * حبل كريمة ملفوف على الحافة العلوية، أقواس متدلية (كشكش + خيط رفيع + حبات)،
 * شرّابة ووردة عند كل التقاء، نقاط عمودية تحت الشرّابة، وصدف على الحافة السفلية.
 */
function vintage(tiers, group, color) {
    const piping = [], beads = [];

    tiers.forEach(t => {
        const h = t.y1 - t.y0;
        const s = Math.max(0.0042, Math.min(0.0065, t.r * 0.06)); // حجم الزخرفة حسب الدور

        // حبل ملفوف: حبات مائلة متداخلة
        const ropeR = t.r - s * 0.55;
        const ropeN = Math.round((2 * Math.PI * ropeR) / (s * 1.25));
        for (let i = 0; i < ropeN; i++) {
            const g = new THREE.SphereGeometry(s, 10, 6);
            g.scale(1.45, 0.78, 0.9);
            g.rotateZ(0.65);
            piping.push(place(g, (i / ropeN) * Math.PI * 2, ropeR, t.y1 + s * 0.45));
        }

        // صدف سفلي: رأس منتفخ + ذيل رفيع
        const bs = s * 1.1, shellR = t.r + bs * 0.35;
        const shellN = Math.round((2 * Math.PI * shellR) / (bs * 2.3));
        for (let i = 0; i < shellN; i++) {
            const a = (i / shellN) * Math.PI * 2;
            const head = new THREE.SphereGeometry(bs, 10, 7);
            head.scale(1.05, 0.95, 1.05);
            const tail = new THREE.SphereGeometry(bs * 0.55, 8, 5);
            tail.scale(1.6, 0.8, 1);
            tail.translate(bs * 1.05, -bs * 0.2, 0);
            piping.push(place(head, a, shellR, t.y0 + bs * 0.9), place(tail, a, shellR, t.y0 + bs * 0.9));
        }

        // الأقواس
        const swags = Math.max(5, Math.round((2 * Math.PI * t.r) / 0.075));
        const span = (Math.PI * 2) / swags;
        const yTop = t.y1 - s * 2.2;                         // نقطة تعليق القوس تحت الحبل
        const depth = Math.min(h * 0.32, t.r * 0.42);        // عمق تدلي الكشكش
        const rr = t.r + 0.0012;

        for (let i = 0; i < swags; i++) {
            const a0 = i * span;

            // كشكش: أقراص رفيعة متقاربة مائلة بالتناوب
            const m = 26;
            for (let k = 1; k < m; k++) {
                const u = k / m, y = yTop - depth * Math.sin(Math.PI * u);
                const g = new THREE.SphereGeometry(s * 0.55, 8, 5);
                g.scale(0.55, 1.45, 0.45);
                g.rotateZ(k % 2 ? 0.28 : -0.28);
                piping.push(place(g, a0 + span * u, rr + 0.0008, y));
            }

            // حبات صغيرة تحت الكشكش
            const b = 14;
            for (let k = 2; k < b - 1; k++) {
                const u = k / b, y = yTop - depth * Math.sin(Math.PI * u) - s * 1.05;
                beads.push(place(new THREE.SphereGeometry(s * 0.32, 6, 4), a0 + span * u, rr + 0.001, y));
            }

            // خيط رفيع فوق الكشكش
            const pts = [];
            for (let k = 0; k <= 20; k++) {
                const u = k / 20, a = a0 + span * u, y = yTop - depth * 0.55 * Math.sin(Math.PI * u);
                pts.push(new THREE.Vector3(Math.cos(a) * (rr + 0.0008), y, Math.sin(a) * (rr + 0.0008)));
            }
            piping.push(new THREE.TubeGeometry(new THREE.CatmullRomCurve3(pts), 30, s * 0.17, 5, false));

            // وردة صغيرة عند نقطة التعليق
            const rose = new THREE.SphereGeometry(s * 0.95, 12, 8);
            rose.scale(1, 1, 0.7);
            piping.push(place(rose, a0, rr + s * 0.35, yTop + s * 0.2));

            // شرّابة: خيوط متدلية تحت الوردة
            const tassel = Math.min(depth * 1.05, h * 0.42);
            for (let k = -2; k <= 2; k++) {
                const g = new THREE.CapsuleGeometry(s * 0.2, tassel * 0.8, 2, 5);
                g.rotateZ(k * 0.06);
                g.translate(k * s * 0.28, -tassel * 0.5, 0);
                piping.push(place(g, a0, rr + s * 0.25, yTop - s * 0.4));
            }

            // نقاط عمودية تحت الشرّابة (من الكبير للصغير) إذا بقي مكان فوق الصدف
            let y = yTop - s * 0.4 - tassel - s * 1.2;
            const floor = t.y0 + bs * 2.6;
            [0.42, 0.34, 0.27].forEach(f => {
                if (y - s * f < floor) return;
                beads.push(place(new THREE.SphereGeometry(s * f, 8, 6), a0, rr + 0.001, y));
                y -= s * 1.5;
            });
        }
    });

    const cream = creamMat('PipingMaterial', color, 0.08);
    addMerged(group, 'VintagePiping', piping, cream);
    addMerged(group, 'VintageBeads', beads, cream);
}

// ---------- التركيب ----------

/**
 * @param {string} baseUrl  رابط glb الأساسي (الحجم + عدد الطبقات)
 * @param {{color: string, style?: 'plain'|'vintage', extraHeight?: number}} options
 *        extraHeight: زيادة ارتفاع الكيكة بالمتر (لطبقات أكثر من النموذج الأساسي — دور واحد فقط)
 * @returns {Promise<string>} blob URL لملف glb المركّب
 */
export async function composeCake(baseUrl, { color, style = 'plain', extraHeight = 0 }) {
    const base = await loadBase(baseUrl);
    const tiers = base.tiers.map(t => ({ ...t }));
    if (extraHeight && tiers.length === 1) tiers[0].y1 += extraHeight;

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

    const cakeMat = creamMat('CakeMaterial', color, 0.35);
    tiers.forEach(t => root.add(cakeBody(t, cakeMat)));

    if (style === 'vintage') vintage(tiers, root, color);

    const glb = await exporter.parseAsync(root, { binary: true });
    return URL.createObjectURL(new Blob([glb], { type: 'model/gltf-binary' }));
}
