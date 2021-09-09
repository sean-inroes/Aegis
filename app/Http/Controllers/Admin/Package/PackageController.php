<?php

namespace App\Http\Controllers\Admin\Package;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    //
    public function index(Request $request)
    {
        $packages = Package::paginate(20);
        return view('manage.pages.package.index', compact('packages'));
    }

    public function show($user)
    {

    }

    public function create()
    {
        return view('manage.pages.package.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'price' => 'required|numeric',
        ], [
            'price.required' => "가격은 필수 항목입니다.",
            'price.numeric' => "가격은 숫자로 입력해주십시오."
        ]);

        $inputs = $request->all();

        $inputs['status'] = 1;

        return;

        $package = Package::create($inputs);

        return redirect()->route('admin.package.index');
    }

    public function edit($id)
    {
        $package = Package::find($id);
        return view('manage.pages.package.edit', compact('package'));
    }

    public function update($package, Request $request)
    {
        $request->validate([
            'mining' => 'required|regex:/^\d+(\.\d{1,2})?$/'
        ]);

        $package = Package::find($package);
        $package->mining = $request->get('mining');
        $package->save();

        return redirect()->route('admin.package.index')->with('success', '변경했습니다.');
    }

    public function destory()
    {

    }
}
