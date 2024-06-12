<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SubCategory;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::latest('id')->with('product_images');
        if (!empty($request->get('keyword'))) {
            $products = $products->where('title', 'like', '%' . $request->get('keyword') . '%');
        }
        $products = $products->paginate(10);
        $data['products'] = $products;
        return view('admin.products.list', $data);
    }

    //Create Products
    public function create()
    {
        $data = [];
        $categories = Category::orderBy('name', 'ASC')->get();
        $brands = Brand::orderBy('name', 'ASC')->get();
        $data['categories']  = $categories;
        $data['brands']  = $brands;
        return view('admin.products.create', $data);
    }

    //Store Products
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
                    $destinationPath = public_path() . '/uploads/products/largeImage/' . $imageName;
                    $image = Image::make($sourcePath);
                    $image->resize(1400, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save($destinationPath);

                    //Small Image
                    $destinationPath = public_path() . '/uploads/products/smallImage/' . $imageName;
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
    public function edit($productId, Request $request)
    {
        $product = Product::find($productId);
        if (empty($product)) {
            return redirect()->route('products.index')->with('Fail', 'Product not found');
        }

        //Fetch Product Image
        $productImages = ProductImage::where('product_id', $product->id)->get();

        $subCategories = SubCategory::where('category_id', $product->category_id)->get();

        $data = [];
        $categories = Category::orderBy('name', 'ASC')->get();
        $brands = Brand::orderBy('name', 'ASC')->get();
        $data['product']  = $product;
        $data['subCategories']  = $subCategories;
        $data['productImages']  = $productImages;
        $data['categories']  = $categories;
        $data['brands']  = $brands;
        return view('admin.products.edit', $data);
    }

    //Update product
    public function update($id, Request $request)
    {
        $product = Product::find($id);

        if (empty($product)) {
            session()->flash('Fail', 'Product not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Product not found',
            ]);
        }
        $rules = [
            'title' => 'required',
            'slug' => 'required | unique:products,slug,' . $product->id . ',id',
            'price' => 'required | numeric',
            'sku' => 'required | unique:products,sku,' . $product->id . ',id',
            'track_quantity' => 'required | in:Yes,No',
            'category' => 'required | numeric',
            'is_featured' => 'required | in:Yes,No',
        ];
        if (!empty($request->track_quantity) && $request->track_quantity == "Yes") {
            $rules['quantity'] = 'required | numeric';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
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

            session()->flash('Success', 'Product updated successfully');

            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    //Delete Product
    public function destroy($productId, Request $request)
    {
        $product = Product::find($productId);

        if (empty($product)) {
            session()->flash('Fail', 'Product not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
            ]);
        }

        $productImages = ProductImage::where('product_id', $productId)->get();

        if (!empty($productImages)) {
            foreach ($productImages as $productImage) {
                File::delete(public_path('/uploads/products/largeImage/' . $productImage->image));
                File::delete(public_path('/uploads/products/smallImage/' . $productImage->image));
            }
            ProductImage::where('product_id', $productId)->delete();
        }

        $product->delete();

        session()->flash('Success', 'Product deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
