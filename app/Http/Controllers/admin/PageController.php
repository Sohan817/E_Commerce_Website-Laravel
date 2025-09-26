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

    //Create Pages
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

    //Update page
    public function edit($pageId, Request $request)
    {
        $page = Page::find($pageId);
        if ($page == null) {
            session()->flash('Fail', 'Page not found');
            return redirect()->route('admin.pages.index');
            return response()->json([
                'status' => true,
                'message' => "Page not found",

            ]);
        }
        $data['page'] = $page;
        return view('admin.pages.edit', $data);
    }
    public function update($pageId, Request $request)
    {
        $page = Page::find($pageId);
        if ($page == null) {
            session()->flash('Fail', 'Page not found');
            return response()->json([
                'status' => true,
                'message' => "Page not found",

            ]);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required',
        ]);
        if ($validator->passes()) {
            $page->name = $request->name;
            $page->slug = $request->slug;
            $page->content = $request->content;
            $page->save();
            session()->flash('Success', 'Page Updated successfully');

            return response()->json([
                'status' => true,
                'message' => 'Page Updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    //Delete pages
    public function destroy($pageId)
    {
        $page = Page::find($pageId);

        if (empty($page)) {
            session()->flash('Fail', 'Page not found');
            return response()->json([
                'status' => true,
                'message' => "Page not found",
            ]);
        }

        $page->delete();

        session()->flash('Success', 'Page deleted successfully');

        return response()->json([
            'status' => true,
            'message' => "Page deleted successfully",
        ]);
    }
}
