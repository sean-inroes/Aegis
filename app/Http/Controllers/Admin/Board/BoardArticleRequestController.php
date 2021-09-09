<?php

namespace App\Http\Controllers\Admin\Board;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardArticle;
use App\Models\BoardArticleParameter;
use App\Models\BoardArticlePeriod;
use App\Models\BoardArticleRequest;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BoardArticleRequestController extends Controller
{
    public function index()
    {
        $boardarticlerequests = BoardArticleRequest::with('board')
            ->with('boardarticle')
            ->paginate(20);

        return view('manage.pages.board.request.index', compact('boardarticlerequests'));
    }

    public function create($id)
    {
        $board = Board::find($id);

        return view('manage.pages.board.request.create', compact('board'));
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $inputs = $request->all();

        $parameters = json_decode($inputs['parameter']);
        $periods = json_decode($inputs['period']);

        $inputs['parameter'] = null;
        $inputs['period'] = null;

        $base64_image = $inputs['thumbnail'];

        if (preg_match('/^data:image\/(\w+);base64,/', $base64_image)) {
            $data = substr($base64_image, strpos($base64_image, ',') + 1);
            $data = base64_decode($data);

            $uuid = (string) Str::uuid();
            $ext = "webp";
            $path = "$uuid.$ext";

            $encoded_image = Image::make($data)->encode('webp', 70);

            if(Storage::disk("public")->put($path, $encoded_image))
            {
                $inputs['thumbnail'] = Storage::disk('public')->url($path);
            }
            else
            {
                $inputs['thumbnail'] = null;
            }
        }
        else
        {
            $inputs['thumbnail'] = null;
        }

        $inputs['started_at'] = $inputs['started_at'] === null ? null : Carbon::parse($inputs['started_at'])->tz('Asia/Seoul')->timestamp;
        $inputs['ended_at'] = $inputs['ended_at'] === null ? null : Carbon::parse($inputs['ended_at'])->tz('Asia/Seoul')->timestamp;

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
        $site = Board::find($id);
        $boardarticle = BoardArticle::find($article);
        $boardarticleparameter = BoardArticleParameter::where('board_article_id', $boardarticle->id)->get();
        $boardarticleperiod = BoardArticlePeriod::where('board_article_id', $boardarticle->id)->get();

        return view('manage.pages.board.request.show', compact('site', 'boardarticle', 'boardarticleparameter', 'boardarticleperiod'));
    }

    public function edit($id, $article)
    {
        $site = Board::find($id);
        $boardarticle = BoardArticle::find($article);
        $boardarticleparameter = BoardArticleParameter::where('board_article_id', $boardarticle->id)->get();
        $boardarticleperiod = BoardArticlePeriod::where('board_article_id', $boardarticle->id)->get();

        return view('manage.pages.board.request.edit', compact('site', 'boardarticle', 'boardarticleparameter', 'boardarticleperiod'));
    }

    public function update(Request $request, $id, $article)
    {
        $site = Board::find($id);

        $request->validate([
            'name' => 'required',
            'url' => 'required',
            'code' => 'required',
        ]);

        $inputs = $request->all();

        $base64_image = $inputs['thumbnail'];

        if($base64_image != $site->thumbnail)
        {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64_image)) {
                $data = substr($base64_image, strpos($base64_image, ',') + 1);
                $data = base64_decode($data);

                $uuid = (string) Str::uuid();
                $ext = "webp";
                $path = "$uuid.$ext";

                $encoded_image = Image::make($data)->encode('webp', 70);

                if(Storage::disk("public")->put($path, $encoded_image))
                {
                    $inputs['thumbnail'] = Storage::disk('public')->url($path);
                }
                else
                {
                    $inputs['thumbnail'] = null;
                }
            }
            else
            {
                $inputs['thumbnail'] = null;
            }
        }

        $site->name = $inputs['name'];
        $site->url = $inputs['url'];
        $site->code = $inputs['code'];
        $site->thumbnail = $inputs['thumbnail'];
        $site->content = $inputs['content'];
        $site->sport = $inputs['sport'];
        $site->minigame = $inputs['minigame'];
        $site->casino = $inputs['casino'];
        $site->save();

        return redirect()->route('admin.board.show', ['site' => $site->id]);
    }

    public function destroy($id)
    {
        Board::destroy($id);

        return redirect()->route('admin.board.index');
    }
}
