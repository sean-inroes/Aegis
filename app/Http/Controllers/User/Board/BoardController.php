<?php

namespace App\Http\Controllers\User\Board;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardArticle;
use App\Models\BoardArticleParameter;
use App\Models\BoardArticlePeriod;
use App\Models\BoardArticleReply;
use App\Models\BoardArticleRequest;
use App\Models\BoardArticleRequestParameter;
use App\Models\BoardArticleSite;
use App\Models\BoardCategory;
use App\Models\Site;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Torann\LaravelMetaTags\Facades\MetaTag;

class BoardController extends Controller
{
    public function index($forum, Request $request)
    {
        $board = Board::where('url', $forum)->first();

        if($board == null) return null;

        $boardcategories = BoardCategory::where('board_id', $board->id)->get();
        $query_category = false;
        $selected_category = $request->has('category') ? $request->get('category') : null;
        $selected_category_index = null;

        if($board->category)
        {
            $query_category = true;

            if($selected_category == null)
            {
                if(count($boardcategories) > 0)
                {
                    $selected_category = $boardcategories[0]->category;
                    $selected_category_index = $boardcategories[0]->id;
                }
            }
            else
            {
                foreach($boardcategories as $category)
                {
                    if($category->category == $selected_category)
                    {
                        $selected_category_index = $category->id;
                    }
                }
            }

            if($selected_category_index == null)
            {
                //카테고리 없음
                return null;
            }
        }

        $boardarticles = BoardArticle::where('board_id', $board->id)
            ->where(function ($query) {
                $query->where('started_at', '<=', Carbon::now()->timestamp)
                    ->orWhere('started_at', null);
            })
            ->where(function ($query) {
                $query->where('ended_at', '>', Carbon::now()->timestamp)
                    ->orWhere('ended_at', null);
            })
            ->where(function ($query) use ($query_category, $selected_category_index) {
                if($query_category)
                {
                    $query->where('board_category_id', $selected_category_index);
                }
            })
            ->orderby('order', 'asc')
            ->orderby('created_at', 'desc')
            ->paginate(20);

            MetaTag::set('title', "$board->name | 도쿄핫");
            MetaTag::set('description', $board->description);

        return view('user.pages.board.index', compact('board', 'boardcategories', 'boardarticles', 'selected_category'));
    }

    public function show($forum, $id)
    {
        $board = Board::where('url', $forum)->first();
        $boardarticle = BoardArticle::where('id', $id)->first();
        $boardarticlesites = BoardArticleSite::where('board_article_id', $id)->get()->toArray();
        $sites = Site::whereIn('id', $boardarticlesites)->get();
        $boardarticleparameter = BoardArticleParameter::where('board_article_id', $boardarticle->id)->get();
        $boardarticlereplies = BoardArticleReply::where('board_article_id', $boardarticle->id)->get();
        $bool = false;

        if($boardarticle->BoardArticlePeriod()->count() > 0)
        {
            $periods = $boardarticle->BoardArticlePeriod()->get();

            $now = Carbon::now()->timestamp;
            $today = Carbon::today('Asia/Seoul')->timestamp;
            $carbon = Carbon::today('Asia/Seoul');
            $bool = true;

            foreach($periods as $period)
            {
                $from = $period->from * 60 * 60 + $today;
                $to = ($period->to == 0 ? 24 : $period->to) * 60 * 60 + $today;

                if($period->type == 0)
                {
                    if($from <= $now && $to > $now)
                    {
                        $bool = false;
                        break;
                    }
                }
                elseif($period->type == 1)
                {
                    if($carbon->isWeekday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 2)
                {
                    if($carbon->isWeekend())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 3)
                {
                    if($carbon->isMonday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 4)
                {
                    if($carbon->isTuesday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 5)
                {
                    if($carbon->isWednesday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 6)
                {
                    if($carbon->isThursday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 7)
                {
                    if($carbon->isFriday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 8)
                {
                    if($carbon->isSaturday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                else
                {
                    if($carbon->isSunday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
            }
        }

        MetaTag::set('title', "$boardarticle->name | 도쿄핫");
        MetaTag::set('description', $boardarticle->description);

        return view('user.pages.board.show', compact('sites', 'board', 'boardarticle', 'boardarticleparameter', 'bool', 'boardarticlereplies'));
    }

    public function create($forum, Request $request)
    {
        $board = Board::where('url', $forum)->first();
        if($board == null) return null;

        $boardcategories = BoardCategory::where('board_id', $board->id)->get();
        $query_category = false;
        $selected_category = $request->has('category') ? $request->get('category') : null;
        $selected_category_index = null;

        if($board->category)
        {
            $query_category = true;

            if($selected_category == null)
            {
                if(count($boardcategories) > 0)
                {
                    $selected_category = $boardcategories[0]->category;
                    $selected_category_index = $boardcategories[0]->id;
                }
            }
            else
            {
                foreach($boardcategories as $category)
                {
                    if($category->category == $selected_category)
                    {
                        $selected_category_index = $category->id;
                    }
                }
            }

            if($selected_category_index == null)
            {
                //카테고리 없음
                return null;
            }
        }

        MetaTag::set('title', "$board->name | 도쿄핫");
        MetaTag::set('description', $board->description);

        return view('user.pages.board.create', compact('board', 'selected_category_index'));
    }

    public function store($forum, Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'nickname' => 'required',
            'password' => 'required',
            'name' => 'required',
            'content' => 'required',
        ]);

        $board = Board::where('url', $forum)->first();
        $inputs = $request->all();
        $boardarticle = BoardArticle::create($inputs);

        return redirect()->route('user.forum.show', ['forum' => $board->url, 'id' => $boardarticle->id]);
    }

    public function process($forum, $id, $process, Request $request)
    {
        $board = Board::where('url', $forum)->first();
        $boardarticle = BoardArticle::where('id', $id)->first();
        $boardarticlesites = BoardArticleSite::where('board_article_id', $id)->get()->toArray();
        $sites = Site::whereIn('id', $boardarticlesites)->get();
        $boardarticleparameter = BoardArticleParameter::where('board_article_id', $boardarticle->id)->get();
        $boardarticlereplies = BoardArticleReply::where('board_article_id', $boardarticle->id)->get();
        $bool = false;

        if($process == "edit")
        {
            $route = route('user.forum.edit', ['forum' => $forum, 'id' => $boardarticle->id]);
        }
        else
        {
            $route = route('user.forum.destory', ['forum' => $forum, 'id' => $boardarticle->id]);
        }

        return view('user.pages.board.confirm', compact('board', 'boardarticle', 'route'));
    }

    public function edit($forum, $id, Request $request)
    {
        $board = Board::where('url', $forum)->first();
        $boardarticle = BoardArticle::where('id', $id)->first();

        $password = $request->get('password');

        if($boardarticle->password == $password)
        {
            return view('user.pages.board.edit', compact('board', 'boardarticle', 'password'));
        }
        elseif($boardarticle->user_id != 2)
        {
            return redirect()->back()->with('error', '접근할 수 없는 게시글입니다.');
        }
        else
        {
            return redirect()->back()->with('error', '비밀번호가 다릅니다.');
        }
    }

    public function update($forum, $id, Request $request)
    {
        $request->validate([
            'password' => 'required',
            'name' => 'required',
            'content' => 'required',
        ]);

        $board = Board::where('url', $forum)->first();
        $boardarticle = BoardArticle::where('id', $id)->first();

        $password = $request->get('password');

        if($password == $boardarticle->password)
        {
            $boardarticle->name = $request->get('name');
            $boardarticle->content = $request->get('content');
            $boardarticle->save();

            return redirect()->route('user.forum.show', ['forum' => $board->url, 'id' => $boardarticle->id])->with('success', '상태를 변경했습니다.');
        }
        elseif($boardarticle->user_id != 2)
        {
            return redirect()->route('user.forum.show', ['forum' => $board->url, 'id' => $boardarticle->id])->with('error', '접근할 수 없는 게시글입니다.');
        }
        else
        {
            return redirect()->route('user.forum.show', ['forum' => $board->url, 'id' => $boardarticle->id])->with('error', '비밀번호가 다릅니다.');
        }
    }

    public function destory($forum, $id, Request $request)
    {
        $board = Board::where('url', $forum)->first();
        $boardarticle = BoardArticle::where('id', $id)->first();

        $password = $request->get('password');

        if($boardarticle->password == $password)
        {
            $boardarticle->delete();
            return redirect()->route('user.forum.index', ['forum' => $board->url])->with('success', '게시글을 삭제했습니다.');
        }
        elseif($boardarticle->user_id != 2)
        {
            return redirect()->back()->with('error', '접근할 수 없는 게시글입니다.');
        }
        else
        {
            return redirect()->back()->with('error', '비밀번호가 다릅니다.');
        }
    }

    public function request($forum, $id, Request $request)
    {
        $board = Board::where('url', $forum)->first();
        $boardarticle = BoardArticle::where('id', $id)->first();

        $boardarticlerequest = BoardArticleRequest::create([
            'board_id' => $board->id,
            'board_article_id' => $boardarticle->id,
        ]);

        $inputs = $request->all();

        foreach($inputs as $key => $item)
        {
            if($key === "_token") continue;

            BoardArticleRequestParameter::create([
                'board_article_request_id' => $boardarticlerequest->id,
                'type' => 0,
                'name' => $key,
                'value' => $item
            ]);
        }
    }

    public function eventindex()
    {
        MetaTag::set('title', '이벤트 | 도쿄핫');
        MetaTag::set('description', '5천 명이 시청하고 있는 무료 티비 - 무료스포츠중계│해외스포츠중계│MLB중계│NBA중계│EPL중계│NPB중계│해외축구중계│사이트 도쿄핫티비입니다. 실시간 스포츠 중계 해외 스포츠중계 방송 영화 드라마 예능을 무료로 시청 가능합니다.');

        $board = Board::where('id', 1)->first();
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
            ->get();

        return view('user.pages.event.index', compact('board', 'boardarticles'));
    }

    public function eventall()
    {
        $board = Board::where('id', 1)->first();
        $boardarticles = BoardArticle::where('board_id', $board->id)->get();

        return view('user.pages.event.all', compact('board', 'boardarticles'));
    }

    public function eventsearch()
    {
        MetaTag::set('title', '이벤트 | 도쿄핫');
        MetaTag::set('description', '5천 명이 시청하고 있는 무료 티비 - 무료스포츠중계│해외스포츠중계│MLB중계│NBA중계│EPL중계│NPB중계│해외축구중계│사이트 도쿄핫티비입니다. 실시간 스포츠 중계 해외 스포츠중계 방송 영화 드라마 예능을 무료로 시청 가능합니다.');

        $sites = Site::get();
        return view('user.pages.event.search', compact('sites'));
    }

    public function eventsearching(Request $request)
    {
        MetaTag::set('title', '이벤트 | 도쿄핫');
        MetaTag::set('description', '5천 명이 시청하고 있는 무료 티비 - 무료스포츠중계│해외스포츠중계│MLB중계│NBA중계│EPL중계│NPB중계│해외축구중계│사이트 도쿄핫티비입니다. 실시간 스포츠 중계 해외 스포츠중계 방송 영화 드라마 예능을 무료로 시청 가능합니다.');

        $boardarticlerequests = BoardArticleRequest::with('board')
            ->with('boardarticle')
            ->with('boardarticlerequestreject')
            ->where('board_article_requests.site', $request->get('site'))
            ->where('board_article_requests.nickname', $request->get('nickname'))
            ->get();

        return view('user.pages.event.searching', compact('boardarticlerequests'));
    }

    public function eventshow($id)
    {
        MetaTag::set('title', '이벤트 | 도쿄핫');
        MetaTag::set('description', '5천 명이 시청하고 있는 무료 티비 - 무료스포츠중계│해외스포츠중계│MLB중계│NBA중계│EPL중계│NPB중계│해외축구중계│사이트 도쿄핫티비입니다. 실시간 스포츠 중계 해외 스포츠중계 방송 영화 드라마 예능을 무료로 시청 가능합니다.');

        $board = Board::where('id', 1)->first();
        $boardarticle = BoardArticle::where('id', $id)->first();
        $boardarticlesites = BoardArticleSite::select('site_id')->where('board_article_id', $id)->get()->toArray();
        $sites = Site::whereIn('id', $boardarticlesites)->get();
        $boardarticleparameter = BoardArticleParameter::where('board_article_id', $boardarticle->id)->get();
        $boardarticlereplies = BoardArticleReply::where('board_article_id', $boardarticle->id)->get();
        $bool = false;

        if($boardarticle->BoardArticlePeriod()->count() > 0)
        {
            $periods = $boardarticle->BoardArticlePeriod()->get();

            $now = Carbon::now()->timestamp;
            $today = Carbon::today('Asia/Seoul')->timestamp;
            $carbon = Carbon::today('Asia/Seoul');
            $bool = true;

            foreach($periods as $period)
            {
                $from = $period->from * 60 * 60 + $today;
                $to = ($period->to == 0 ? 24 : $period->to) * 60 * 60 + $today;

                if($period->type == 0)
                {
                    if($from <= $now && $to > $now)
                    {
                        $bool = false;
                        break;
                    }
                }
                elseif($period->type == 1)
                {
                    if($carbon->isWeekday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 2)
                {
                    if($carbon->isWeekend())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 3)
                {
                    if($carbon->isMonday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 4)
                {
                    if($carbon->isTuesday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 5)
                {
                    if($carbon->isWednesday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 6)
                {
                    if($carbon->isThursday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 7)
                {
                    if($carbon->isFriday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 8)
                {
                    if($carbon->isSaturday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                else
                {
                    if($carbon->isSunday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
            }
        }

        return view('user.pages.event.show', compact('sites', 'board', 'boardarticle', 'boardarticleparameter', 'bool', 'boardarticlereplies'));
    }

    public function eventreply($id)
    {

    }

    public function eventrequest($id, Request $request)
    {
        $inputs = $request->all();

        $boardarticle = BoardArticle::where('id', $id)->first();
        $bool = false;

        if($boardarticle->BoardArticlePeriod()->count() > 0)
        {
            $periods = $boardarticle->BoardArticlePeriod()->get();

            $now = Carbon::now()->timestamp;
            $today = Carbon::today('Asia/Seoul')->timestamp;
            $carbon = Carbon::today('Asia/Seoul');
            $bool = true;

            foreach($periods as $period)
            {
                $from = $period->from * 60 * 60 + $today;
                $to = ($period->to == 0 ? 24 : $period->to) * 60 * 60 + $today;

                if($period->type == 0)
                {
                    if($from <= $now && $to > $now)
                    {
                        $bool = false;
                        break;
                    }
                }
                elseif($period->type == 1)
                {
                    if($carbon->isWeekday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 2)
                {
                    if($carbon->isWeekend())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 3)
                {
                    if($carbon->isMonday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 4)
                {
                    if($carbon->isTuesday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 5)
                {
                    if($carbon->isWednesday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 6)
                {
                    if($carbon->isThursday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 7)
                {
                    if($carbon->isFriday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                elseif($period->type == 8)
                {
                    if($carbon->isSaturday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
                else
                {
                    if($carbon->isSunday())
                    {
                        if($from <= $now && $to > $now)
                        {
                            $bool = false;
                            break;
                        }
                    }
                }
            }
        }

        if($bool)
        {
            return redirect()->back()->with('error', "신청할 수 있는 기간이 아닙니다.");
        }

        $boardarticlerequest = BoardArticleRequest::create([
            'board_id' => $boardarticle->board_id,
            'board_article_id' => $boardarticle->id,
            'site' => $inputs['site'],
            'nickname' => $inputs['nickname'],
        ]);

        $inputs['site'] = null;
        $inputs['nickname'] = null;

        foreach($inputs as $key => $item)
        {
            if($key === "_token") continue;
            if($item === null) continue;

            $parameter = BoardArticleParameter::find($key);
            BoardArticleRequestParameter::create([
                'board_article_request_id' => $boardarticlerequest->id,
                'type' => 0,
                'value' => $item,
                'label' => $parameter->name,
                'name' => $parameter->value,
            ]);
        }

        return redirect()->back()->with('success', "성공적으로 신청했습니다.");
    }
}
