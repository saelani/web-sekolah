<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    use HasFactory;

    protected $table = 'web_downloads';

    protected $fillable = ['title', 'file_path', 'file_size', 'download_count'];
}
