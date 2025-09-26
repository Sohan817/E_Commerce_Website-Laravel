<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $pages = Page::latest();

        if (!empty($request->get('keyword'))) {
            $pages = $pages->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $pages = $pages->paginate();
        $data['pages'] = $pages;
        return view('admin.pages.list', $data);
    }
    public function create()
    {
        return view('admin.pages.create');
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required',
        ]);
        if ($validator->passes()) {
            $page = new Page();
            $page->name = $request->name;
            $page->slug = $request->slug;
            $page->content = $request->content;
            $page->save();
            session()->flash('Success', 'Page created successfully');

            return response()->json([
                'status' => true,
                'message' => 'Page created successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function edit() {}
    public function update() {}
    public function destroy() {}
}
