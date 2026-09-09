<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول المقاسات المتاحة لكل كيكة.
     * حاليًا يدعم "دور واحد" فقط بـ 4 مقاسات.
     * لاحقًا لدعم "دورين"/"3 أدوار"، يُضاف حقل tier_count (1/2/3)
     * وحقل combo_label (مثل "10/8" أو "8/6/4") بدل diameter_cm المفرد.
     */
    public function up(): void
    {
        Schema::create('cake_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cake_id')->constrained('cakes')->cascadeOnDelete();

            $table->unsignedTinyInteger('tier_count')->default(1); // عدد الأدوار (1 حاليًا فقط)
            $table->decimal('diameter_cm', 6, 2);   // قطر الدور (مفرد حاليًا)
            $table->decimal('height_cm', 6, 2)->default(8); // ثابت 8 سم لكل دور

            $table->string('size_label');            // "4", "6", "8", "10" (بالإنش)
            $table->unsignedSmallInteger('serves_min');
            $table->unsignedSmallInteger('serves_max');

            $table->string('model_glb_path');
            $table->string('model_usdz_path')->nullable();

            $table->boolean('is_default')->default(false); // المقاس المحدد افتراضيًا (زي "الأكثر طلبًا")
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
        });

        // خيار مستقل: عدد طبقات الحشو الداخلي (1-7) — لا يؤثر على نموذج AR
        Schema::create('cake_layer_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('layers_count'); // 1 إلى 7
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cake_layer_options');
        Schema::dropIfExists('cake_variants');
    }
};
