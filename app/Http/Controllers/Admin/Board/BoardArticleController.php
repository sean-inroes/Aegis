<?php

namespace App\Http\Controllers\Admin\Board;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardArticle;
use App\Models\BoardArticleParameter;
use App\Models\BoardArticlePeriod;
use App\Models\BoardArticleReply;
use App\Models\BoardArticleRequest;
use App\Models\BoardArticleRequestParameter;
use App\Models\BoardArticleRequestReject;
use App\Models\BoardArticleSite;
use App\Models\BoardCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BoardArticleController extends Controller
{
    public function index($id)
    {
        $board = Board::find($id);
        $boardArticles = BoardArticle::orderby('order', 'asc')->orderby('created_at', 'desc')->where('board_id', $id)->withCount('boardarticlerequest')->withCount('boardarticlereply')->paginate(20);

        return view('manage.pages.board.article.index', compact('board', 'boardArticles'));
    }

    public function create($id)
    {
        $board = Board::find($id);
        $boardcategories = BoardCategory::where('board_id', $id)->get();

        return view('manage.pages.board.article.create', compact('board', 'boardcategories'));
    }

    public function store(Request $request, $id)
    {
        $board = Board::find($id);

        if ($board->type)
        {
            $request->validate([
                'name' => 'required',
            ]);
        }
        else
        {
            $request->validate([
                'name' => 'required',
                'thumbnail' => 'required',
            ]);
        }

        if($board->category)
        {
            $request->validate([
                'board_category_id' => 'required',
            ]);
        }

        $inputs = $request->all();

        $parameters = json_decode($inputs['parameter']);
        $periods = json_decode($inputs['period']);

        $inputs['parameter'] = null;
        $inputs['period'] = null;

        $inputs['started_at'] = $inputs['started_at'] === null ? null : Carbon::parse($inputs['started_at'])->tz('Asia/Seoul')->timestamp;
        $inputs['ended_at'] = $inputs['ended_at'] === null ? null : Carbon::parse($inputs['ended_at'])->tz('Asia/Seoul')->timestamp;

        $inputs['nickname'] = Auth::user()->nickname;

        $boardarticle = BoardArticle::create($inputs);

        foreach($parameters as $parameter)
        {
            BoardArticleParameter::create([
                'board_article_id' => $boardarticle->id,
                'type' => $parameter->type,
                'value' => $parameter->value,
                'name' => $parameter->name,
            ]);
        }

        foreach($periods as $period)
        {
            BoardArticlePeriod::create([
                'board_article_id' => $boardarticle->id,
                'type' => $period->type,
                'from' => $period->from,
                'to' => $period->to,
            ]);
        }

        if($board->id == 1)
        {
            $sites = json_decode($inputs['sites']);
            foreach($sites as $site)
            {
                BoardArticleSite::create([
                    'board_article_id' => $boardarticle->id,
                    'site_id' => $site
                ]);
            }
        }

        if($boardarticle)
        {
            return redirect()->route('admin.board.article.show', ['id' => $id, 'article' => $boardarticle->id]);
        }
        else
        {
            return redirect()->back()->with('error', '문제가 있어서 추가하지 못했습니다.');
        }
    }

    public function show($id, $article)
    {
        $board = Board::find($id);
        $boardarticle = BoardArticle::where('id', $article)->withCount('boardarticlerequest')->withCount('boardarticlereply')->first();
        $boardarticleparameter = BoardArticleParameter::where('board_article_id', $boardarticle->id)->get();
        $boardarticleperiod = BoardArticlePeriod::where('board_article_id', $boardarticle->id)->get();

        return view('manage.pages.board.article.show', compact('board', 'boardarticle', 'boardarticleparameter', 'boardarticleperiod'));
    }

    public function edit($id, $article)
    {
        $board = Board::find($id);
        $boardarticle = BoardArticle::find($article);
        $boardcategories = BoardCategory::where('board_id', $id)->get();
        $boardarticleparameter = BoardArticleParameter::where('board_article_id', $boardarticle->id)->get();
        $boardarticleperiod = BoardArticlePeriod::where('board_article_id', $boardarticle->id)->get();

        return view('manage.pages.board.article.edit', compact('board', 'boardarticle', 'boardarticleparameter', 'boardarticleperiod', 'boardcategories'));
    }

    public function update(Request $request, $id, $article)
    {
        $board = Board::find($id);
        $boardarticle = BoardArticle::find($article);

        $inputs = $request->all();

        $parameters = json_decode($inputs['parameter']);
        $periods = json_decode($inputs['period']);

        $inputs['parameter'] = null;
        $inputs['period'] = null;

        $inputs['started_at'] = $inputs['started_at'] === null ? null : Carbon::parse($inputs['started_at'])->tz('Asia/Seoul')->timestamp;
        $inputs['ended_at'] = $inputs['ended_at'] === null ? null : Carbon::parse($inputs['ended_at'])->tz('Asia/Seoul')->timestamp;

        $boardarticle->name = $inputs['name'];

        if($inputs['thumbnail'] != null)
        {
            $boardarticle->thumbnail = $inputs['thumbnail'];
        }

        if($board->category) $boardarticle->board_category_id = $inputs['board_category_id'];

        $boardarticle->description = $inputs['description'];
        $boardarticle->content = $inputs['content'];
        $boardarticle->order = $inputs['order'];
        $boardarticle->started_at = $inputs['started_at'];
        $boardarticle->ended_at = $inputs['ended_at'];
        $boardarticle->save();

        BoardArticleParameter::where('board_article_id', $boardarticle->id)->delete();

        foreach($parameters as $parameter)
        {
            BoardArticleParameter::create([
                'board_article_id' => $boardarticle->id,
                'type' => $parameter->type,
                'value' => $parameter->value,
                'name' => $parameter->name,
            ]);
        }

        BoardArticlePeriod::where('board_article_id', $boardarticle->id)->delete();

        foreach($periods as $period)
        {
            BoardArticlePeriod::create([
                'board_article_id' => $boardarticle->id,
                'type' => $period->type,
                'from' => $period->from,
                'to' => $period->to,
            ]);
        }

        if($board->id == 1)
        {
            BoardArticleSite::where('board_article_id', $boardarticle->id)->delete();

            $sites = json_decode($inputs['sites']);
            foreach($sites as $site)
            {
                BoardArticleSite::create([
                    'board_article_id' => $boardarticle->id,
                    'site_id' => $site
                ]);
            }
        }

        return redirect()->route('admin.board.article.show', ['id' => $board->id, 'article' => $boardarticle->id]);
    }

    public function destroy($id, $article)
    {
        BoardArticle::destroy($article);

        return redirect()->route('admin.board.article.index', ['id' => $id]);
    }

    public function replyindex($id, $article)
    {
        $board = Board::find($id);
        $boardarticle = BoardArticle::where('id', $article)->withCount('boardarticlerequest')->withCount('boardarticlereply')->first();
        $boardarticlereplies = BoardArticleReply::where('board_article_id', $boardarticle->id)->paginate(20);

        return view('manage.pages.board.article.reply', compact('board', 'boardarticle', 'boardarticlereplies'));
    }

    public function replyishow($id, $article, $reply)
    {
        $board = Board::find($id);
        $boardarticle = BoardArticle::find($article);
        $boardarticleparameter = BoardArticleParameter::where('board_article_id', $boardarticle->id)->get();
        $boardarticleperiod = BoardArticlePeriod::where('board_article_id', $boardarticle->id)->get();

        return view('manage.pages.board.article.show', compact('board', 'boardarticle', 'boardarticleparameter', 'boardarticleperiod'));
    }

    public function requestindex($id, $article)
    {
        $board = Board::find($id);
        $boardarticle = BoardArticle::where('id', $article)->withCount('boardarticlerequest')->withCount('boardarticlereply')->first();
        $boardarticlerequests = BoardArticleRequest::with('boardarticlerequestreject')->where('board_article_id', $boardarticle->id)->paginate(20);
        //dd($boardarticlerequests);
        return view('manage.pages.board.article.request', compact('board', 'boardarticle', 'boardarticlerequests'));
    }

    public function requestshow($id, $article, $request)
    {
        $request = BoardArticleRequest::with('boardarticlerequestparameter')->with('boardarticlerequestreject')->find($request);
        return view('manage.pages.board.article.requestshow', compact('request'));
    }

    public function requestedit($id, $article, $request)
    {
        $board = Board::find($id);
        $boardarticle = BoardArticle::find($article);
        $boardarticlerequest = BoardArticleRequest::find($request);
        return view('manage.pages.board.article.requestedit', compact('board', 'boardarticle', 'boardarticlerequest'));
    }

    public function requestupdate($id, $article, $request, Request $input)
    {
        if($input->get('status') == 3)
        {
            $input->validate([
                'status' => 'required',
                'reason' => 'required',
            ]);
        }
        else
        {
            $input->validate([
                'status' => 'required',
            ]);
        }

        $request = BoardArticleRequest::find($request);
        $request->status = $input->get('status');
        $request->save();

        if($input->get('status') == 3)
        {
            BoardArticleRequestReject::create([
                'board_article_request_id' => $request->id,
                'reason' => $input->get('reason')
            ]);
        }

        return redirect()->back();
    }
}
