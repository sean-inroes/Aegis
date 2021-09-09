<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function index(Request $request)
    {
        if($request->has('type'))
        {
            if($request->get('type'))
            {
                $users = User::role('회원')->orderby('id', 'desc')->paginate(20);
            }
            else
            {
                $users = User::role('관리자')->orderby('id', 'desc')->paginate(20);
            }
        }
        else
        {
            $users = User::orderby('id', 'desc')->paginate(20);
        }

        return view('manage.pages.user.index', compact('users'));
    }

    public function show($user)
    {
        $user = User::find($user);

        return view('manage.pages.user.show', compact('user'));
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
