<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'is_read',
        'ip_address',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }
}
