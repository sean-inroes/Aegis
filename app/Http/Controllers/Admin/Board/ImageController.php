<?php

namespace App\Http\Controllers\Admin\Board;

use App\Http\Controllers\Controller;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function index($type)
    {
        if($type == "site")
        {
            $x_ratio = 5;
            $y_ratio = 3;
            $auto = 0;
        }
        elseif($type == "cover")
        {
            $x_ratio = 5;
            $y_ratio = 1;
            $auto = 0;
        }
        elseif($type == "profile")
        {
            $x_ratio = 1;
            $y_ratio = 1;
            $auto = 0;
        }
        elseif($type == "nation")
        {
            $x_ratio = 1;
            $y_ratio = 1;
            $auto = 1;
        }
        else
        {
            return "<script>window.close();</script>";
        }

        return view('manage.pages.board.editor', compact(
            'type',
            'x_ratio',
            'y_ratio',
            'auto',
        ));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        $image = $request->file('image');

        $uuid = (string) Str::uuid();
        $ext = "webp";
        $path = "$uuid.$ext";

        $encoded_image = Image::make($image->path())->encode('webp', 100);

        if(Storage::disk("public")->put($path, $encoded_image))
        {
            return $path = Storage::disk('public')->url($path);
        }
    }

    public function dropzone(Request $request, $type = "default")
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);

        $image = $request->file('file');

        if($image->getMimeType() == "image/gif")
        {
            $uuid = (string) Str::uuid();
            $ext = "gif";
            $path = "$uuid.$ext";

            $image->move('storage/', $path);
            return $path = Storage::disk('public')->url($path);
        }
        else
        {
            $uuid = (string) Str::uuid();
            $ext = "webp";
            $path = "$uuid.$ext";

            if($type == "inth")
            {
                $encoded_image = Image::make($image->path())->resize(900, 600)->encode('webp', 100);
            }
            elseif($type == "outh")
            {
                $encoded_image = Image::make($image->path())->resize(500, 300)->encode('webp', 100);
            }
            else
            {
                $encoded_image = Image::make($image->path())->encode('webp', 100);
            }

            if(Storage::disk("public")->put($path, $encoded_image))
            {
                return $path = Storage::disk('public')->url($path);
            }
        }


    }
}
