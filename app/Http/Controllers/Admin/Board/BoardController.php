<?php

namespace App\Http\Controllers\Admin\Board;

use App\Http\Controllers\Controller;
use App\Models\Board;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index()
    {
        $boards = Board::withCount('boardarticle')->withCount('boardarticlerequest')->withCount('boardarticlereply')->get();
        return view('manage.pages.board.index', compact('boards'));
    }

    public function create()
    {
        return view('manage.pages.board.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'url' => 'required',
            'icon' => 'required',
            'description' => 'required',
            'type' => 'required',
            'reply' => 'required',
            'parameter' => 'required',
            'write' => 'required',
            'guest' => 'required',
            'category' => 'required'
        ]);

        $inputs = $request->all();

        $board = Board::create($inputs);

        if($board)
        {
            return redirect()->route('admin.board.article.index', ['id' => $board->id]);
        }
        else
        {
            return redirect()->back()->with('error', '문제가 있어서 추가하지 못했습니다.');
        }
    }

    public function show($id)
    {
        $site = Board::find($id);
        return view('manage.pages.board.show', compact('site'));
    }

    public function edit($id)
    {
        $board = Board::find($id);
        return view('manage.pages.board.edit', compact('board'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'url' => 'required',
            'icon' => 'required',
            'description' => 'required',
            'type' => 'required',
            'reply' => 'required',
            'parameter' => 'required',
            'write' => 'required',
            'guest' => 'required',
            'category' => 'required'
        ]);

        $inputs = $request->all();

        $board = Board::find($id);

        $board->name = $inputs['name'];
        $board->url = $inputs['url'];
        $board->icon = $inputs['icon'];
        $board->description = $inputs['description'];
        $board->type = $inputs['type'];
        $board->reply = $inputs['reply'];
        $board->parameter = $inputs['parameter'];
        $board->write = $inputs['write'];
        $board->guest = $inputs['guest'];
        $board->category = $inputs['category'];

        $board->save();

        return redirect()->route('admin.board.index');
    }

    public function destroy($id)
    {

        $board = Board::find($id);
        if($board->lock)
        {

            return redirect()->route('admin.board.index')->with('error', '해당 게시판은 삭제할 수 없습니다.');
        }

        $board->delete();

        return redirect()->route('admin.board.index');
    }
}
