<?php

namespace Database\Seeders;

use App\Models\Cake;
use Illuminate\Database\Seeder;

class CakeSeeder extends Seeder
{
    public function run(): void
    {
        Cake::create([
            'name' => 'Signature Cake',
            'slug' => 'signature-cake',
            'description' => 'كيكة فوندان أملس بتصميم كلاسيكي بسيط',
            'diameter_cm' => 10.16,
            'height_cm' => 8,
            'model_glb_path' => 'models/cake.glb',
            'model_usdz_path' => null,
            'reference_image_path' => 'images/signature-cake.jpeg',
            'is_active' => true,
        ]);
    }
}
