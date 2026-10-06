"""
توليد نسخ ملوّنة من ملفات .usdz (للواقع المعزز على الآيفون).

الآيفون يفتح ملف usdz جاهز من السيرفر ولا يأخذ اللون من الصفحة،
لذلك نولّد لكل لون نسخة من كل نموذج بتغيير diffuseColor لمواد CakeMaterial فقط
(البورد واللوجو يبقون كما هم).

الاستخدام:
    pip install usd-core
    python3 tools/generate_color_usdz.py

الألوان لازم تطابق config/cake_customizer.php (المفتاح + كود اللون).
الناتج: storage/app/public/models/cake_<size>_layers<n>_<version>_<color>.usdz
"""

import glob
import os
import tempfile

from pxr import Gf, Usd, UsdShade, UsdUtils

MODEL_VERSION = "v45"
MODELS_DIR = os.path.join(os.path.dirname(__file__), "..", "storage", "app", "public", "models")

COLORS = {
    "blue": "#1F4A8C",
    "red": "#C21E2E",
}


def srgb_to_linear(c: float) -> float:
    return c / 12.92 if c <= 0.04045 else ((c + 0.055) / 1.055) ** 2.4


def hex_to_linear(hex_color: str) -> Gf.Vec3f:
    h = hex_color.lstrip("#")
    return Gf.Vec3f(*(srgb_to_linear(int(h[i:i + 2], 16) / 255) for i in (0, 2, 4)))


def make_variant(src: str, color_key: str, hex_color: str) -> str:
    stage = Usd.Stage.Open(src)
    changed = 0
    for prim in stage.Traverse():
        if prim.GetTypeName() == "Material" and prim.GetName().startswith("CakeMaterial"):
            for child in prim.GetChildren():
                shader = UsdShade.Shader(child)
                if shader and shader.GetIdAttr().Get() == "UsdPreviewSurface":
                    shader.GetInput("diffuseColor").Set(hex_to_linear(hex_color))
                    changed += 1
    if not changed:
        raise RuntimeError(f"no CakeMaterial found in {src}")

    out = src[: -len(".usdz")] + f"_{color_key}.usdz"
    with tempfile.TemporaryDirectory() as tmp:
        usdc = os.path.join(tmp, os.path.basename(out).replace(".usdz", ".usdc"))
        stage.GetRootLayer().Export(usdc)
        if os.path.exists(out):
            os.remove(out)
        if not UsdUtils.CreateNewARKitUsdzPackage(usdc, out):
            raise RuntimeError(f"failed to package {out}")
    return out


def main() -> None:
    sources = sorted(glob.glob(os.path.join(MODELS_DIR, f"cake_*_{MODEL_VERSION}.usdz")))
    if not sources:
        raise SystemExit(f"no {MODEL_VERSION} usdz files in {MODELS_DIR}")
    for src in sources:
        for key, hex_color in COLORS.items():
            print(os.path.basename(make_variant(src, key, hex_color)))


if __name__ == "__main__":
    main()
