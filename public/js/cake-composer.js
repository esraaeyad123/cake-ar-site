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

// ---------- محيط الكيكة (دائري أو قلب) ----------

/**
 * محيط مغلق على مستوى x/z كنقاط متقاربة، مع طول كل جزء والاتجاه للخارج،
 * حتى توزَّع الزخرفة بالتساوي على أي شكل.
 */
function makeOutline(points) {
    // اتجاه الرسم (مع/عكس عقارب الساعة) لتحديد الخارج
    let area = 0;
    points.forEach((p, i) => { const q = points[(i + 1) % points.length]; area += p.x * q.z - q.x * p.z; });
    const sign = area > 0 ? 1 : -1;

    const n = points.length, cum = [0];
    for (let i = 0; i < n; i++) {
        const p = points[i], q = points[(i + 1) % n];
        cum.push(cum[i] + Math.hypot(q.x - p.x, q.z - p.z));
    }
    const normals = points.map((_, i) => {
        const a = points[(i - 1 + n) % n], b = points[(i + 1) % n];
        const tx = b.x - a.x, tz = b.z - a.z, l = Math.hypot(tx, tz) || 1;
        return { nx: sign * tz / l, nz: -sign * tx / l };
    });

    return {
        length: cum[n],
        // نقطة على المحيط بعد مسافة d من البداية
        at(d) {
            d = ((d % cum[n]) + cum[n]) % cum[n];
            let lo = 0, hi = n;
            while (hi - lo > 1) { const mid = (lo + hi) >> 1; if (cum[mid] <= d) lo = mid; else hi = mid; }
            const u = (d - cum[lo]) / ((cum[lo + 1] - cum[lo]) || 1);
            const p = points[lo], q = points[(lo + 1) % n], np = normals[lo], nq = normals[(lo + 1) % n];
            const nx = np.nx + (nq.nx - np.nx) * u, nz = np.nz + (nq.nz - np.nz) * u, l = Math.hypot(nx, nz) || 1;
            return { x: p.x + (q.x - p.x) * u, z: p.z + (q.z - p.z) * u, nx: nx / l, nz: nz / l };
        },
    };
}

function circleOutline(r) {
    const pts = [];
    for (let i = 0; i < 256; i++) {
        const a = (i / 256) * Math.PI * 2;
        pts.push({ x: Math.cos(a) * r, z: Math.sin(a) * r });
    }
    return makeOutline(pts);
}

// قلب بعرض 2r، رأسه المدبب باتجاه الكاميرا (+z)
function heartPoints(r, count = 240) {
    const k = r / 16, pts = [];
    for (let i = 0; i < count; i++) {
        const t = (i / count) * Math.PI * 2;
        const x = 16 * Math.pow(Math.sin(t), 3);
        const y = 13 * Math.cos(t) - 5 * Math.cos(2 * t) - 2 * Math.cos(3 * t) - Math.cos(4 * t);
        pts.push({ x: k * x, z: -k * (y + 2.5) });
    }
    return pts;
}

// نجمة بخمس رؤوس بعرض 2r، رأس للورا (-z) ورأسين للقدام، وزواياها مدوّرة شوي (تنعيم Chaikin)
function starPoints(r) {
    let pts = [];
    for (let i = 0; i < 10; i++) {
        const a = -Math.PI / 2 + (i / 10) * Math.PI * 2, rad = i % 2 ? r * 0.5 : r;
        pts.push({ x: Math.cos(a) * rad, z: Math.sin(a) * rad });
    }
    // تقطيع خفيف للزوايا (مو 0.25 حتى ما تصير مثل الوردة)، ثم نقاط متقاربة على كل ضلع للزخرفة
    for (let k = 0; k < 2; k++) {
        const next = [];
        pts.forEach((p, i) => {
            const q = pts[(i + 1) % pts.length];
            next.push({ x: p.x * 0.9 + q.x * 0.1, z: p.z * 0.9 + q.z * 0.1 }, { x: p.x * 0.1 + q.x * 0.9, z: p.z * 0.1 + q.z * 0.9 });
        });
        pts = next;
    }
    // التنعيم يقصّر الرؤوس، فنرجّع أبعد رأس لنص القطر
    const scale = r / Math.max(...pts.map(p => Math.hypot(p.x, p.z)));
    return pts.map(p => ({ x: p.x * scale, z: p.z * scale }));
}

// نص دائرة من قدام (z ≥ 0) مع الظهر المستقيم — محيط الوال كيك الملصوق على البورد
function halfCirclePoints(r) {
    const pts = [];
    for (let i = 0; i <= 128; i++) {
        const a = -Math.PI / 2 + (i / 128) * Math.PI;
        pts.push({ x: Math.sin(a) * r, z: Math.cos(a) * r });
    }
    for (let i = 1; i < 32; i++) pts.push({ x: r - (i / 32) * 2 * r, z: 0 });
    return pts;
}

// يوجّه شكلًا محليًا عند نقطة على المحيط: x على طول المحيط، y للأعلى، z نحو الداخل.
// offset: بُعد عن المحيط للخارج (سالب = للداخل)
function place(g, p, offset, y) {
    g.rotateY(-(Math.atan2(p.nz, p.nx) + Math.PI / 2));
    g.translate(p.x + p.nx * offset, y, p.z + p.nz * offset);
    return g;
}

// ---------- جسم الكيكة ----------

function bevelSize(tier) {
    return Math.min(0.009, (tier.y1 - tier.y0) * 0.14, tier.r * 0.12);
}

// half: نص الكيكة الأمامي فقط (الوال كيك)
function roundBody(tier, material, half = false) {
    const { r, y0, y1 } = tier;
    const e = bevelSize(tier);
    const pts = [new THREE.Vector2(0, y0), new THREE.Vector2(r, y0), new THREE.Vector2(r, y1 - e)];
    for (let i = 1; i <= 10; i++) {
        const a = (i / 10) * Math.PI / 2;
        pts.push(new THREE.Vector2(r - e + Math.cos(a) * e, y1 - e + Math.sin(a) * e));
    }
    pts.push(new THREE.Vector2(0, y1));
    const g = half ? new THREE.LatheGeometry(pts, 48, -Math.PI / 2, Math.PI) : new THREE.LatheGeometry(pts, 96);
    return new THREE.Mesh(g, material);
}

// شكل ممدود للأعلى (قلب، نجمة) بحافة علوية دائرية
function extrudedBody(tier, material, outlinePoints) {
    const { r, y0, y1 } = tier;
    const e = bevelSize(tier);
    const shape = new THREE.Shape(outlinePoints(r - e).map(p => new THREE.Vector2(p.x, -p.z)));
    const g = new THREE.ExtrudeGeometry(shape, {
        depth: (y1 - y0) - 2 * e, bevelEnabled: true, bevelThickness: e, bevelSize: e, bevelSegments: 5, curveSegments: 1,
    });
    g.rotateX(-Math.PI / 2);   // العمق يصير للأعلى
    g.translate(0, y0 + e, 0);
    g.computeVertexNormals();
    return new THREE.Mesh(g, material);
}

// مخروط مثل قبعة الحفلة: جوانب منتفخة قليلًا ورأس دائري
function coneBody(tier, material) {
    const { r, y0, y1 } = tier;
    const pts = [new THREE.Vector2(0, y0)];
    for (let i = 0; i <= 40; i++) {
        const t = i / 40;
        pts.push(new THREE.Vector2(r * Math.pow(1 - Math.pow(t, 1.3), 0.75), y0 + (y1 - y0) * t));
    }
    return new THREE.Mesh(new THREE.LatheGeometry(pts, 96), material);
}

// سن ست: نص دائرة واقفة على البورد (مثل الشمس وقت الغروب)، العرض = قطر المقاس،
// والسماكة من قدام لورا = ارتفاع الطبقات
function sunsetBody(tier, material) {
    const { r, y0, y1 } = tier;
    const thick = y1 - y0;
    const e = Math.min(0.008, thick * 0.14);
    const shape = new THREE.Shape();
    shape.moveTo(-(r - e), 0);
    shape.absarc(0, 0, r - e, Math.PI, 0, true);
    shape.lineTo(-(r - e), 0);
    const g = new THREE.ExtrudeGeometry(shape, {
        depth: thick - 2 * e, bevelEnabled: true, bevelThickness: e, bevelSize: e, bevelSegments: 5, curveSegments: 64,
    });
    // الحافة السفلية المستقيمة تنزل للبورد، والوجه المقوس باتجاه الكاميرا
    g.translate(0, e, -(thick - 2 * e) / 2);
    g.computeBoundingBox();
    g.translate(0, y0 - g.boundingBox.min.y, 0);
    g.computeVertexNormals();
    return new THREE.Mesh(g, material);
}

/**
 * وال كيك: نص كيكة أدوار (الوجه المدوّر للأمام) ملصوقة على بورد مربع واقف ومايل شوي لورا،
 * مثل البرواز، واللوجو تحت على اليسار. root = الكيكة والزخرفة كما بُنيت على الأرض.
 */
function wallScene(root, tiers, logo, boardColor) {
    const rB = tiers[0].r;
    const S = 2 * rB * 1.5;                  // ضلع البورد
    const T = 0.012;                         // سماكة البورد
    const baseH = 0.008;                     // قاعدة نص دائرية تحت الكيكة
    // الكيكة فوق سطر اللوجو (مثل الصورة: اللوجو تحت على اليسار)
    const logoH = logo ? logo.box.max.z - logo.box.min.z : 0;
    const lift = S * 0.04 + logoH + 0.006;
    const boardMat = mat('BoardMaterial', boardColor, 0.95);

    const wall = new THREE.Group();
    wall.name = 'WallCake';

    const cake = new THREE.Group();
    [...root.children].forEach(c => cake.add(c));
    cake.position.y = lift + baseH - tiers[0].y0;
    wall.add(cake);

    const board = new THREE.Mesh(new THREE.BoxGeometry(S, S, T), boardMat);
    board.geometry.translate(0, S / 2, -T / 2);
    board.name = 'Board';
    wall.add(board);

    const base = new THREE.Mesh(new THREE.CylinderGeometry(rB * 1.04, rB * 1.04, baseH, 48, 1, false, -Math.PI / 2, Math.PI), boardMat);
    base.geometry.translate(0, lift + baseH / 2, 0);
    base.name = 'CakeBase';
    wall.add(base);

    if (logo) {
        const { canvas, box } = logo;
        const tex = new THREE.CanvasTexture(canvas);
        tex.colorSpace = THREE.SRGBColorSpace;
        tex.flipY = false;
        const w = box.max.x - box.min.x, h = box.max.z - box.min.z;
        const plane = new THREE.PlaneGeometry(w, h);
        const uv = plane.attributes.uv;
        for (let i = 0; i < uv.count; i++) uv.setY(i, 1 - uv.getY(i));
        plane.translate(-S / 2 + S * 0.05 + w / 2, S * 0.04 + h / 2, 0.0004);
        const mesh = new THREE.Mesh(plane, new THREE.MeshStandardMaterial({ name: 'LogoMaterial', map: tex, roughness: 0.9 }));
        mesh.name = 'Logo';
        wall.add(mesh);
    }

    // مايل لورا 10 درجات، مسنود على حافته السفلية
    wall.rotation.x = -THREE.MathUtils.degToRad(10);
    wall.position.y = T * Math.sin(THREE.MathUtils.degToRad(10));
    root.add(wall);
}

// ---------- الزخرفة ----------

// صدف: رأس منتفخ + ذيل رفيع على طول المحيط
function shellBorder(outline, y, bs, out) {
    const n = Math.round(outline.length / (bs * 2.3));
    for (let i = 0; i < n; i++) {
        const p = outline.at((i / n) * outline.length);
        const head = new THREE.SphereGeometry(bs, 10, 7);
        head.scale(1.05, 0.95, 1.05);
        const tail = new THREE.SphereGeometry(bs * 0.55, 8, 5);
        tail.scale(1.6, 0.8, 1);
        tail.translate(bs * 1.05, -bs * 0.2, 0);
        out.push(place(head, p, bs * 0.35, y), place(tail, p, bs * 0.35, y));
    }
}

/**
 * مثل كيكات الفنتج الكلاسيكية:
 * حبل كريمة ملفوف على الحافة العلوية، أقواس متدلية (كشكش + خيط رفيع + حبات)،
 * شرّابة ووردة عند كل التقاء، نقاط عمودية تحت الشرّابة، وصدف على الحافة السفلية.
 */
function vintage(tiers, piping, beads) {
    tiers.forEach(t => {
        const h = t.y1 - t.y0, o = t.outline, L = o.length;
        const s = Math.max(0.0042, Math.min(0.0065, t.r * 0.06)); // حجم الزخرفة حسب الدور

        // حبل ملفوف: حبات مائلة متداخلة
        const ropeN = Math.round(L / (s * 1.25));
        for (let i = 0; i < ropeN; i++) {
            const g = new THREE.SphereGeometry(s, 10, 6);
            g.scale(1.45, 0.78, 0.9);
            g.rotateZ(0.65);
            piping.push(place(g, o.at((i / ropeN) * L), -s * 0.55, t.y1 + s * 0.45));
        }

        const bs = s * 1.1;
        shellBorder(o, t.y0 + bs * 0.9, bs, piping);

        // الأقواس
        const swags = Math.max(5, Math.round(L / 0.075));
        const span = L / swags;
        const yTop = t.y1 - s * 2.2;                         // نقطة تعليق القوس تحت الحبل
        const depth = Math.min(h * 0.32, t.r * 0.42);        // عمق تدلي الكشكش
        const rr = 0.0012;

        for (let i = 0; i < swags; i++) {
            const d0 = i * span, p0 = o.at(d0);

            // كشكش: أقراص رفيعة متقاربة مائلة بالتناوب
            const m = 26;
            for (let k = 1; k < m; k++) {
                const u = k / m, y = yTop - depth * Math.sin(Math.PI * u);
                const g = new THREE.SphereGeometry(s * 0.55, 8, 5);
                g.scale(0.55, 1.45, 0.45);
                g.rotateZ(k % 2 ? 0.28 : -0.28);
                piping.push(place(g, o.at(d0 + span * u), rr + 0.0008, y));
            }

            // حبات صغيرة تحت الكشكش
            const b = 14;
            for (let k = 2; k < b - 1; k++) {
                const u = k / b, y = yTop - depth * Math.sin(Math.PI * u) - s * 1.05;
                beads.push(place(new THREE.SphereGeometry(s * 0.32, 6, 4), o.at(d0 + span * u), rr + 0.001, y));
            }

            // خيط رفيع فوق الكشكش
            const pts = [];
            for (let k = 0; k <= 20; k++) {
                const u = k / 20, p = o.at(d0 + span * u), y = yTop - depth * 0.55 * Math.sin(Math.PI * u);
                pts.push(new THREE.Vector3(p.x + p.nx * (rr + 0.0008), y, p.z + p.nz * (rr + 0.0008)));
            }
            piping.push(new THREE.TubeGeometry(new THREE.CatmullRomCurve3(pts), 30, s * 0.17, 5, false));

            // وردة صغيرة عند نقطة التعليق
            const rose = new THREE.SphereGeometry(s * 0.95, 12, 8);
            rose.scale(1, 1, 0.7);
            piping.push(place(rose, p0, rr + s * 0.35, yTop + s * 0.2));

            // شرّابة: خيوط متدلية تحت الوردة
            const tassel = Math.min(depth * 1.05, h * 0.42);
            for (let k = -2; k <= 2; k++) {
                const g = new THREE.CapsuleGeometry(s * 0.2, tassel * 0.8, 2, 5);
                g.rotateZ(k * 0.06);
                g.translate(k * s * 0.28, -tassel * 0.5, 0);
                piping.push(place(g, p0, rr + s * 0.25, yTop - s * 0.4));
            }

            // نقاط عمودية تحت الشرّابة (من الكبير للصغير) إذا بقي مكان فوق الصدف
            let y = yTop - s * 0.4 - tassel - s * 1.2;
            const floor = t.y0 + bs * 2.6;
            [0.42, 0.34, 0.27].forEach(f => {
                if (y - s * f < floor) return;
                beads.push(place(new THREE.SphereGeometry(s * f, 8, 6), p0, rr + 0.001, y));
                y -= s * 1.5;
            });
        }
    });
}

// ---------- التركيب ----------

/**
 * @param {string} baseUrl  رابط glb الأساسي (الحجم + عدد الطبقات) — للقلب والمخروطي والسن ست والوال كيك نموذج دور واحد
 * @param {{color: string, boardColor?: string, shape?: 'round'|'heart'|'star'|'cone'|'sunset'|'wall', style?: 'plain'|'vintage', extraHeight?: number}} options
 *        extraHeight: زيادة ارتفاع الكيكة بالمتر (لطبقات أكثر من النموذج الأساسي — دور واحد فقط)
 * @returns {Promise<string>} blob URL لملف glb المركّب
 */
export async function composeCake(baseUrl, { color, boardColor = '#FFFFFF', shape = 'round', style = 'plain', extraHeight = 0 }) {
    const base = await loadBase(baseUrl);
    const tiers = base.tiers.map(t => ({ ...t }));
    if (extraHeight && tiers.length === 1) tiers[0].y1 += extraHeight;
    if (shape === 'cone') tiers[0].y1 = tiers[0].y0 + tiers[0].r * 2.9; // ارتفاع ثابت حسب المقاس
    const isWall = shape === 'wall';
    tiers.forEach(t => {
        t.outline = shape === 'heart' ? makeOutline(heartPoints(t.r))
            : shape === 'star' ? makeOutline(starPoints(t.r))
            : isWall ? makeOutline(halfCirclePoints(t.r))
            : circleOutline(t.r);
    });

    const root = new THREE.Group();
    root.name = 'JOMACAKE';

    // الوال كيك له بورد واقف خاص (wallScene)
    if (base.board && !isWall) {
        const g = base.board.geo.clone().toNonIndexed();
        g.deleteAttribute('color');
        g.computeVertexNormals();
        const board = new THREE.Mesh(g, mat('BoardMaterial', boardColor, 0.95));
        board.name = 'Board';
        root.add(board);
    }

    if (base.logo && !isWall) {
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
    const body = {
        round: roundBody,
        heart: (t, m) => extrudedBody(t, m, heartPoints),
        star: (t, m) => extrudedBody(t, m, starPoints),
        cone: coneBody,
        sunset: sunsetBody,
        wall: (t, m) => roundBody(t, m, true),
    }[shape] || roundBody;
    tiers.forEach(t => {
        const mesh = body(t, cakeMat);
        mesh.name = 'CakeTier';
        root.add(mesh);
    });

    const piping = [], beads = [];
    if (shape === 'cone') {
        // المخروطي: صدف على القاعدة فقط (مثل كيكة قبعة الحفلة)
        const t = tiers[0], bs = Math.max(0.005, Math.min(0.008, t.r * 0.075));
        shellBorder(t.outline, t.y0 + bs * 0.9, bs, piping);
    } else if (style === 'vintage' && shape !== 'sunset') {
        vintage(tiers, piping, beads);
    }
    const cream = creamMat('PipingMaterial', color, 0.08);
    addMerged(root, 'Piping', piping, cream);
    addMerged(root, 'Beads', beads, cream);

    if (isWall) wallScene(root, tiers, base.logo, boardColor);

    const glb = await exporter.parseAsync(root, { binary: true });
    return URL.createObjectURL(new Blob([glb], { type: 'model/gltf-binary' }));
}
