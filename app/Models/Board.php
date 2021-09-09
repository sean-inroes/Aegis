<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'url',
        'icon',
        'description',
        'type',
        'reply',
        'parameter',
        'single',
        'category',
        'write',
        'guest',
        'status',
        'lock',
    ];

    protected $dateFormat = 'U';

    public function boardarticle()
    {
        return $this->hasMany(BoardArticle::class);
    }

    public function boardarticlerequest()
    {
        return $this->hasMany(BoardArticleRequest::class);
    }

    public function boardarticlereply()
    {
        return $this->hasMany(BoardArticleReply::class);
    }
}
