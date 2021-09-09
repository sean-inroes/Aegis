<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'board_id',
        'board_category_id',
        'nickname',
        'password',
        'name',
        'thumbnail',
        'description',
        'content',
        'order',
        'started_at',
        'ended_at',
    ];

    protected $dateFormat = 'U';

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function board(){
        return $this->belongsTo(Board::class);
    }

    public function boardcategory(){
        return $this->belongsTo(BoardCategory::class);
    }

    public function boardarticlereply()
    {
        return $this->hasMany(BoardArticleReply::class);
    }

    public function boardarticlerequest()
    {
        return $this->hasMany(BoardArticleRequest::class);
    }

    public function boardarticleperiod()
    {
        return $this->hasMany(BoardArticlePeriod::class);
    }
}
