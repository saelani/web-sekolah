<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSlider extends Model
{
    use HasFactory;

    protected $table = 'web_hero_sliders';

    protected $fillable = ['title', 'subtitle', 'image_path', 'button_text', 'button_url', 'order_number', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
