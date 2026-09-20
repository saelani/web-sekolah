<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gallery extends Model
{
    use HasFactory;

    protected $table = 'web_galleries';

    protected $fillable = ['title', 'slug', 'description'];

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class, 'gallery_id');
    }
}
