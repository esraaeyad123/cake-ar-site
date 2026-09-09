<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CakeVariant extends Model
{
    protected $fillable = [
        'cake_id',
        'tier_count',
        'diameter_cm',
        'height_cm',
        'size_label',
        'serves_min',
        'serves_max',
        'model_glb_path',
        'model_usdz_path',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'diameter_cm' => 'float',
        'height_cm' => 'float',
        'is_default' => 'boolean',
    ];

    public function cake()
    {
        return $this->belongsTo(Cake::class);
    }

    // ملاحظة: لازم تضيف بملف app/Models/Cake.php هذا الميثود:
    // public function variants() { return $this->hasMany(CakeVariant::class); }

    public function getGlbUrlAttribute(): string
    {
        return asset('storage/' . $this->model_glb_path);
    }

    public function getUsdzUrlAttribute(): ?string
    {
        return $this->model_usdz_path ? asset('storage/' . $this->model_usdz_path) : null;
    }

    public function getServesLabelAttribute(): string
    {
        return $this->serves_min === $this->serves_max
            ? "{$this->serves_min} أشخاص"
            : "{$this->serves_min}-{$this->serves_max} أشخاص";
    }
}
