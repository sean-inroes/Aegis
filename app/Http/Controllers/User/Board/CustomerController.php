<?php

namespace App\Http\Controllers\User\Board;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardArticle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Torann\LaravelMetaTags\Facades\MetaTag;

class CustomerController extends Controller
{
    public function index()
    {
        MetaTag::set('title', '고객센터 | 도쿄핫');
        MetaTag::set('description', '5천 명이 시청하고 있는 무료 티비 - 무료스포츠중계│해외스포츠중계│MLB중계│NBA중계│EPL중계│NPB중계│해외축구중계│사이트 도쿄핫티비입니다. 실시간 스포츠 중계 해외 스포츠중계 방송 영화 드라마 예능을 무료로 시청 가능합니다.');

        $board = Board::where('id', 2)->first();
        $boardarticles = BoardArticle::where('board_id', $board->id)
            ->where(function ($query) {
                $query->where('started_at', '<', Carbon::now()->timestamp)
                    ->orWhere('started_at', null);
            })
            ->where(function ($query) {
                $query->where('ended_at', '>=', Carbon::now()->timestamp)
                    ->orWhere('ended_at', null);
            })
            ->orderby('order', 'asc')
            ->orderby('created_at', 'desc')
            ->paginate(20);

        return view('user.pages.customer.index', compact('board', 'boardarticles'));
    }
}
