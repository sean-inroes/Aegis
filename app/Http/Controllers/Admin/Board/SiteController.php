<?php

namespace App\Http\Controllers\Admin\Board;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Models\SiteCategoryTemplate;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::orderby('order', 'asc')->orderby('created_at', 'desc')->get();
        return view('manage.pages.site.index', compact('sites'));
    }

    public function create()
    {
        $categorytemplates = SiteCategoryTemplate::get();
        return view('manage.pages.site.create', compact('categorytemplates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'in_description' => 'required',
            'out_description' => 'required',
            'url' => 'required',
            'code' => 'required',
            'in_thumbnail' => 'required',
            'out_thumbnail' => 'required',
        ]);

        $inputs = $request->all();

        if($inputs['tags'] != null)
        {
            $tags = json_decode($inputs['tags']);
            foreach($tags as $index => $tag)
            {
                if($index == 0)
                {
                    $inputs['tags'] = $tag->value;
                }
                else
                {
                    $inputs['tags'] .= ",$tag->value";
                }
            }
        }

        $site = Site::create($inputs);

        if($inputs['categories'] != null)
        {
            $categories = json_decode($inputs['categories']);
            foreach($categories as $category)
            {
                SiteCategory::create([
                    'site_id' => $site->id,
                    'type' => $category->type,
                    'icon' => $category->icon,
                    'name' => $category->name,
                    'content' => $category->content,
                ]);
            }
        }

        return redirect()->route('admin.site.show', ['site' => $site->id]);
    }

    public function show($id)
    {
        $site = Site::find($id);
        $sitecategories = SiteCategory::where('site_id', $id)->get();
        return view('manage.pages.site.show', compact('site', 'sitecategories'));
    }

    public function edit($id)
    {
        $site = Site::find($id);
        $categorytemplates = SiteCategoryTemplate::get();
        $sitecategories = SiteCategory::where('site_id', $id)->get();
        return view('manage.pages.site.edit', compact('site', 'categorytemplates', 'sitecategories'));
    }

    public function update(Request $request, $id)
    {
        $site = Site::find($id);

        $request->validate([
            'name' => 'required',
            'in_description' => 'required',
            'out_description' => 'required',
            'url' => 'required',
            'code' => 'required',
        ]);

        $inputs = $request->all();

        $site->name = $inputs['name'];
        $site->in_description = $inputs['in_description'];
        $site->out_description = $inputs['out_description'];
        $site->order = $inputs['order'];
        $site->url = $inputs['url'];
        $site->code = $inputs['code'];

        if($inputs['tags'] != null)
        {
            $tags = json_decode($inputs['tags']);

            foreach($tags as $index => $tag)
            {
                if($index == 0)
                {
                    $inputs['tags'] = $tag->value;
                }
                else
                {
                    $inputs['tags'] .= ",$tag->value";
                }
            }

            $site->tags = $inputs['tags'];
        }

        if($inputs['in_thumbnail'] != null)
        {
            $site->in_thumbnail = $inputs['in_thumbnail'];
        }

        if($inputs['out_thumbnail'] != null)
        {
            $site->out_thumbnail = $inputs['out_thumbnail'];
        }

        SiteCategory::where('site_id', $site->id)->delete();

        if($inputs['categories'] != null)
        {
            $categories = json_decode($inputs['categories']);
            foreach($categories as $category)
            {
                SiteCategory::create([
                    'site_id' => $site->id,
                    'type' => $category->type,
                    'icon' => $category->icon,
                    'name' => $category->name,
                    'content' => $category->content,
                ]);
            }
        }

        $site->save();

        return redirect()->route('admin.site.show', ['site' => $site->id]);
    }

    public function destroy($id)
    {
        Site::destroy($id);

        return redirect()->route('admin.site.index');
    }
}
