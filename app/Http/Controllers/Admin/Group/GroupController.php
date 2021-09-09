<?php

namespace App\Http\Controllers\Admin\Group;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GroupDataController;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    //
    public function index(Request $request)
    {
        $users = User::where('group_id', null)->get();

        return view('manage.pages.group.index', compact('users'));
    }

    public function show($user)
    {
        $users = GroupDataController::searchItem($user);

        return $users;
    }

    public function create()
    {

    }

    public function store()
    {

    }

    public function edit()
    {

    }

    public function update()
    {

    }

    public function destory()
    {

    }
}
