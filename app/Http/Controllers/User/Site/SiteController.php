<?php

namespace App\Http\Controllers\User\Site;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SiteCategory;
use Illuminate\Http\Request;
use Torann\LaravelMetaTags\Facades\MetaTag;

class SiteController extends Controller
{
    public function index()
    {
        MetaTag::set('title', '보증사이트 | 도쿄핫');
        MetaTag::set('description', '5천 명이 시청하고 있는 무료 티비 - 무료스포츠중계│해외스포츠중계│MLB중계│NBA중계│EPL중계│NPB중계│해외축구중계│사이트 도쿄핫티비입니다. 실시간 스포츠 중계 해외 스포츠중계 방송 영화 드라마 예능을 무료로 시청 가능합니다.');

        $sites = Site::orderby('order', 'asc')->orderby('created_at', 'desc')->get();

        return view('user.pages.site.index', compact('sites'));
    }

    public function show($id)
    {


        $site = Site::find($id);
        $sitecategories = SiteCategory::where('site_id', $id)->get();

        MetaTag::set('title', "$site->name | 도쿄핫");
        MetaTag::set('description', $site->out_description);

        return view('user.pages.site.show', compact('site', 'sitecategories'));
    }
}
