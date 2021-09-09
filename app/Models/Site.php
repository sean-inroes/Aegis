<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'in_description',
        'out_description',
        'url',
        'code',
        'in_thumbnail',
        'out_thumbnail',
        'content',
        'sport',
        'minigame',
        'casino',
        'images',
        'tags',
        'stickers',
        'order',
        'status',
    ];

    protected $dateFormat = 'U';

    public function user(){
        return $this->belongsTo(User::class);
    }
}
