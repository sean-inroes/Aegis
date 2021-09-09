<?php

namespace App\Http\Controllers\Admin\Board;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardArticle;
use App\Models\BoardCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = BoardCategory::get();
        return view('manage.pages.board.category.index', compact('categories'));
    }

    public function create()
    {
        $boards = Board::where('category', 1)->get();
        return view('manage.pages.board.category.create', compact('boards'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'board_id' => 'required',
            'category' => 'required'
        ]);

        $category = BoardCategory::create($request->all());

        return redirect()->route('admin.board.category.show', ['category' => $category->id]);
    }

    public function show($category)
    {
        $category = BoardCategory::find($category);

        return view('manage.pages.board.category.show', compact('category'));
    }

    public function edit($category)
    {
        $boards = Board::where('category', 1)->get();
        $category = BoardCategory::find($category);

        return view('manage.pages.board.category.edit', compact('boards', 'category'));
    }

    public function update($category, Request $request)
    {
        $request->validate([
            'board_id' => 'required',
            'category' => 'required',
            'status' => 'required',
        ]);

        $category = BoardCategory::find($category);

        $inputs = $request->all();

        $category->board_id = $inputs['board_id'];
        $category->category = $inputs['category'];
        $category->status = $inputs['status'];

        $category->save();

        return redirect()->route('admin.board.category.show', ['category' => $category->id]);
    }

    public function destroy($category)
    {
        $boardarticles = BoardArticle::where('board_category_id', $category)->count();
        if($boardarticles > 0)
        {
            return redirect()->route('admin.board.category.index')->with('error', "카테고리를 사용하는 게시글이 있어서 삭제하지 못했습니다.");
        }

        BoardCategory::destroy($category);

        return redirect()->route('admin.board.category.index')->with('success', "카테고리를 삭제했습니다.");
    }
}
