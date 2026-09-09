<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cake extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'diameter_cm',
        'height_cm',
        'model_glb_path',
        'model_usdz_path',
        'reference_image_path',
        'is_active',
    ];

    protected $casts = [
        'diameter_cm' => 'float',
        'height_cm' => 'float',
        'is_active' => 'boolean',
    ];

    /**
     * رابط عام لملف الـ glb (يُستخدم في أندرويد و WebXR)
     */
    public function getGlbUrlAttribute(): string
    {
        return asset('storage/' . $this->model_glb_path);
    }

    /**
     * رابط عام لملف الـ usdz (يُستخدم في iOS / AR Quick Look)
     */
    public function getUsdzUrlAttribute(): ?string
    {
        return $this->model_usdz_path
            ? asset('storage/' . $this->model_usdz_path)
            : null;
    }

    public function variants()
{
    return $this->hasMany(CakeVariant::class);
}
}
