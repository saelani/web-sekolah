<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $table = 'web_contact_messages';

    protected $fillable = ['sender_name', 'email_or_phone', 'subject', 'message', 'is_read'];

    protected $casts = ['is_read' => 'boolean'];
}
