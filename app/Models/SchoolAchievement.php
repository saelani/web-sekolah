<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolAchievement extends Model
{
    use HasFactory;

    protected $table = 'web_school_achievements';

    protected $fillable = ['title', 'category', 'level', 'winner_name', 'achievement_date', 'image_path'];

    protected $casts = ['achievement_date' => 'date'];
}