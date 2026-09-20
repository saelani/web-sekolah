<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $table = 'web_pages';

    protected $fillable = ['title', 'slug', 'content', 'image_path', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];
}
