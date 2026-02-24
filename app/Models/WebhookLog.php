<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic',
        'shop_domain',
        'payload',
        'received_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'received_at' => 'datetime',
    ];
}