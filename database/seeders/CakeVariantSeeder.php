<?php

namespace Database\Seeders;

use App\Models\Cake;
use App\Models\CakeVariant;
use Illuminate\Database\Seeder;

class CakeVariantSeeder extends Seeder
{
    public function run(): void
    {
        $cake = Cake::where('slug', 'signature-cake')->first();

        $variants = [
            ['size_label' => '4',  'diameter_cm' => 10.16, 'serves_min' => 1,  'serves_max' => 2,  'model' => 'cake_4in',  'sort_order' => 1],
            ['size_label' => '6',  'diameter_cm' => 15.24, 'serves_min' => 4,  'serves_max' => 6,  'model' => 'cake_6in',  'sort_order' => 2, 'is_default' => true],
            ['size_label' => '8',  'diameter_cm' => 20.32, 'serves_min' => 8,  'serves_max' => 10, 'model' => 'cake_8in',  'sort_order' => 3],
            ['size_label' => '10', 'diameter_cm' => 25.40, 'serves_min' => 15, 'serves_max' => 25, 'model' => 'cake_10in', 'sort_order' => 4],
        ];

        foreach ($variants as $v) {
            CakeVariant::create([
                'cake_id' => $cake->id,
                'tier_count' => 1,
                'diameter_cm' => $v['diameter_cm'],
                'height_cm' => 8,
                'size_label' => $v['size_label'],
                'serves_min' => $v['serves_min'],
                'serves_max' => $v['serves_max'],
                'model_glb_path' => 'models/' . $v['model'] . '.glb',
                'model_usdz_path' => 'models/' . $v['model'] . '.usdz',
                'is_default' => $v['is_default'] ?? false,
                'sort_order' => $v['sort_order'],
            ]);
        }
    }
}
