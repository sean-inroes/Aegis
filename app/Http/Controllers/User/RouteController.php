<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardArticle;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Torann\LaravelMetaTags\Facades\MetaTag;

class RouteController extends Controller
{
    public function index()
    {
        return view('user.pages.index');
    }

    public function tv()
    {
        MetaTag::set('title', '실시간티비 | 도쿄핫');
        MetaTag::set('description', '5천 명이 시청하고 있는 무료 티비 - 무료스포츠중계│해외스포츠중계│MLB중계│NBA중계│EPL중계│NPB중계│해외축구중계│사이트 도쿄핫티비입니다. 실시간 스포츠 중계 해외 스포츠중계 방송 영화 드라마 예능을 무료로 시청 가능합니다.');

        return view('user.pages.tv');
    }
}
