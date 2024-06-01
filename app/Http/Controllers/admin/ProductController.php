<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // $categories = Product::latest();
        // if (!empty($request->get('keyword'))) {
        //     $categories = $categories->where('name', 'like', '%' . $request->get('keyword') . '%');
        // }
        // $categories = $categories->paginate(10);
        // return view('admin.category.list', compact('categories'));
    }
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

            //save products image
            if (!empty($request->image_array)) {
                foreach ($request->image_array as $temm_image_id) {

                    $tempImageInfo = TempImage::find($temm_image_id);
                    $extArray = explode('.', $tempImageInfo->name);
                    $ext = last($extArray);  //like jpg/png/gif
                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = 'NULL';
                    $productImage->save();

                    $imageName = $product->id . '-' . $productImage->id . '-' . time() . '.' . $ext;
                    $productImage->image = $imageName;
                    $productImage->save();

                    //Generate Image Thumbnail

                    //Large Image
                    $sourcePath = public_path() . '/temp/' . $tempImageInfo->name;
                    $destinationPath = public_path() . '/uploads/products/largeImage/' . $tempImageInfo->name;
                    $image = Image::make($sourcePath);
                    $image->resize(1400, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save($destinationPath);

                    //Small Image
                    $destinationPath = public_path() . '/uploads/products/smallImage/' . $tempImageInfo->name;
                    $image = Image::make($sourcePath);
                    $image->fit(300, 300);
                    $image->save($destinationPath);
                }
            }

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
