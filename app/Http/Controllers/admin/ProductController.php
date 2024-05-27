<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function create()
    {
        $data = [];
        $categories = Category::orderBy('name', 'ASC')->get();
        $brands = Brand::orderBy('name', 'ASC')->get();
        $data['categories']  = $categories;
        $data['brands']  = $brands;
        return view('admin.products.create', $data);
    }
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'slug' => 'required | unique:products',
            'price' => 'required | numeric',
            'sku' => 'required | unique:products',
            'track_quantity' => 'required | in:Yes,No',
            'category' => 'required | numeric',
            'is_featured' => 'required | in:Yes,No',
        ];

        if (!empty($request->track_quantity) && $request->track_quantity == "Yes") {
            $rules['quantity'] = 'required | numeric';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {

            $product = new Product();
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_quantity = $request->track_quantity;
            $product->quantity = $request->quantity;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand_id;
            $product->is_featured = $request->is_featured;
            $product->save();

            session()->flash('Success', 'Product added successfully');

            return response()->json([
                'status' => true,
                'message' => 'Product added successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
}
