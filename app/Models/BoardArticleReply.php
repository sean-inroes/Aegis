<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardArticleReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'board_id',
        'board_article_id',
        'comment',
        'status',
    ];

    protected $dateFormat = 'U';

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function board(){
        return $this->belongsTo(Board::class);
    }

    public function boardarticle(){
        return $this->belongsTo(BoardArticle::class);
    }
}
