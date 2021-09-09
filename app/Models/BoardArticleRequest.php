<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardArticleRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'board_article_id',
        'site',
        'nickname',
        'status',
    ];

    protected $dateFormat = 'U';

    public function board(){
        return $this->belongsTo(Board::class);
    }

    public function boardarticle(){
        return $this->belongsTo(BoardArticle::class, 'board_article_id', 'id');
    }

    public function boardarticlerequestparameter()
    {
        return $this->hasMany(BoardArticleRequestParameter::class);
    }

    public function boardarticlerequestreject()
    {
        return $this->hasMany(BoardArticleRequestReject::class);
    }
}
