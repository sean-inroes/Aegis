<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Purchase;
use App\Models\User;
use App\Models\UserLogPoint;
use Illuminate\Http\Request;

class GroupDataController extends Controller
{
    public static function searchItem($item = 1)
    {
        $user = User::find($item);
        $groups = Group::get();

        $arr = [];
        $queue = [];

        foreach($groups as $group)
        {
            if($group->id == $user->group_id)
            {
                array_push($arr, [
                    "key" => $group->id,
                    "name" => $group->revenue,
                    "nation" => $group->member,
                    "title" => $group->member,
                    "headOf" => ""
                ]);
                array_push($queue, $group->id);
                break;
            }
        }

        for($i = 0 ; $i < count($queue) ; $i++)
        {
            $index = $queue[$i];

            foreach($groups as $group)
            {
                if($group->parent_id == $index)
                {
                    array_push($arr, [
                        "key" => $group->id,
                        "boss" => $group->parent_id,
                        "name" => $group->revenue,
                        "nation" => $group->member,
                        "title" => $group->member,
                        "headOf" => ""
                    ]);
                    array_push($queue, $group->id);
                }
            }
        }

        return $arr;
    }

    public static function searchItemUpline(Purchase $purchase)
    {
        $users = User::get();
        $topuser = self::findTopUser($purchase->user->id);
        $logs = UserLogPoint::where('purchase_id', $purchase->id)->get();

        $arr = [];
        $queue = [];

        foreach($users as $user)
        {
            if($user->id == $topuser)
            {
                array_push($arr, [
                    "key" => $user->id,
                    "name" => $user->username,
                    "nation" => $user->photo_url,
                    "title" => "레벨 : ".$user->level,
                    "headOf" => $user->recommend == null ? "" : $user->recommend->username
                ]);
                array_push($queue, $user->id);

                break;
            }
        }

        for($i = 0 ; $i < count($queue) ; $i++)
        {
            $index = $queue[$i];

            foreach($users as $user)
            {
                if($user->group_id == $index)
                {
                    array_push($arr, [
                        "key" => $user->id,
                        "boss" => $user->group_id,
                        "name" => $user->username,
                        "nation" => $user->photo_url,
                        "title" => "레벨 : ".$user->level,
                        "headOf" => $user->recommend->username
                    ]);
                    array_push($queue, $user->id);
                }
            }
        }

        return $arr;
    }

    private static function findTopUser($item)
    {
        $users = User::get();

        $target = $item;

        while(true)
        {
            foreach($users as $user)
            {
                if($user->id == $target)
                {
                    if($user->group_id == null)
                    {
                        return $target;
                    }
                    else
                    {
                        $target = $user->group_id;
                    }
                }
            }
        }
    }
}
