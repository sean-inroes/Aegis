<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardArticlePeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_article_id',
        'type',
        'from',
        'to',
    ];

    protected $dateFormat = 'U';

    public function boardarticle(){
        return $this->belongsTo(BoardArticle::class);
    }
}
