<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolProfile extends Model
{
    use HasFactory;

    protected $table = 'web_school_profiles';

    protected $fillable = ['npsn', 'school_name', 'headmaster_name', 'accreditation', 'address', 'phone', 'email', 'website', 'vision', 'mission', 'logo_path', 'maps_embed'];
}
