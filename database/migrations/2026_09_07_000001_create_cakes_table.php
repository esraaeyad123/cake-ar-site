<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تشغيل الترحيل (Migration).
     *
     * ملاحظة للمستقبل:
     * حاليًا كل كيكة لها قياس ثابت واحد (diameter_cm / height_cm).
     * عند إضافة خيارات متعددة (أحجام / عدد أدوار) لاحقًا،
     * يُفضّل إنشاء جدول منفصل cake_variants يرتبط بـ cakes عبر cake_id،
     * بحيث يحمل كل variant: diameter_cm, height_cm, tiers_count, model_path (glb/usdz) خاص به.
     * هذا يسمح بإضافة الخيارات دون تعديل هيكل الجدول الأساسي.
     */
    public function up(): void
    {
        Schema::create('cakes', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // اسم الكيكة، مثال: Signature Cake
            $table->string('slug')->unique();        // معرف نصي للرابط
            $table->text('description')->nullable();

            // القياسات الحالية (ثابتة الآن)
            $table->decimal('diameter_cm', 6, 2);     // القطر بالسنتيمتر
            $table->decimal('height_cm', 6, 2);       // الارتفاع بالسنتيمتر

            // مسارات ملفات النموذج ثلاثي الأبعاد
            $table->string('model_glb_path');         // نموذج .glb (أندرويد / ويب)
            $table->string('model_usdz_path')->nullable(); // نموذج .usdz (آيفون)

            // صورة مرجعية للعرض قبل تحميل الـ AR
            $table->string('reference_image_path')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cakes');
    }
};
