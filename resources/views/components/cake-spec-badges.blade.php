@props(['tierCount' => 1, 'sizeLabel', 'layersCount' => 1])

{{--
    صف الدلالات البصرية أسفل المجسم — كل بادج (دور/حجم/طبقات) زر يفتح لوحة الخيارات المطابقة له.
    ملاحظة: بادج "عدد الأشخاص" انتقل ليكون عنصر مستقل بزاوية يسار المجسم (شوف serves-badge بالصفحة الرئيسية)
--}}
<div class="cake-spec-badges" dir="rtl">
    <div class="spec-badge clickable" data-target="panel-tier">
        <img src="{{ asset('images/icons/floor.png') }}" alt="عدد الأدوار" class="spec-icon">
        <span class="spec-label">{{ $tierCount == 1 ? 'دور واحد' : $tierCount . ' أدوار' }}</span>
    </div>

    <div class="spec-badge clickable" data-target="panel-size">
        <img src="{{ asset('images/icons/ruler.png') }}" alt="الحجم" class="spec-icon">
        <span class="spec-label">{{ $sizeLabel }} إنش</span>
    </div>

    <div class="spec-badge clickable" data-target="panel-layers">
        <img src="{{ asset('images/icons/layer.png') }}" alt="عدد الطبقات" class="spec-icon">
        <span class="spec-label">{{ $layersCount }} {{ $layersCount == 1 ? 'طبقة' : 'طبقات' }}</span>
    </div>
</div>

<style>
    .cake-spec-badges {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
        gap: 12px;
        padding: 14px 8px;
        border-top: 1px solid #F4C0D1;
    }
    .spec-badge {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        min-width: 60px;
    }
    .spec-badge.clickable {
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 10px;
        transition: background 0.15s ease, transform 0.12s ease;
    }
    .spec-badge.clickable:active { transform: scale(0.94); }
    .spec-badge.clickable.open {
        background: #FBEAF0;
    }
    .spec-icon, .spec-icon-mdi {
        width: 28px;
        height: 28px;
        color: #D4537E;
    }
    .spec-label {
        font-size: 12px;
        font-weight: 700;
        color: #4B1528;
    }
</style>
