<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class ProductImageController extends Controller
{
    public function update(Request $request)
    {
        $image = $request->image;
        $ext = $image->getClientOriginalExtension();
        $sourcePath = $image->getPathName();

        $productImage = new ProductImage();
        $productImage->product_id = $request->product_id;
        $productImage->image = 'NULL';
        $productImage->save();

        $imageName = $request->product_id . '-' . $productImage->id . '-' . time() . '.' . $ext;
        $productImage->image = $imageName;
        $productImage->save();

        //Generate Image Thumbnail

        //Large Image
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

        return response()->json([
            'status' => true,
            'image_id' => $productImage->id,
            'imagePath' => asset('/uploads/products/smallImage/' . $productImage->image),
            'message' => 'Image updated successfully',
        ]);
    }

    public function destroy(Request $request)
    {
        $productImage = ProductImage::find($request->id);
        if (empty($productImage)) {
            return response()->json([
                'status' => false,
                'message' => 'Image not found',
            ]);
        }

        //Delete image from folder
        File::delete(public_path('/uploads/products/largeImage/' . $productImage->image));
        File::delete(public_path('/uploads/products/smallImage/' . $productImage->image));

        $productImage->delete();

        return response()->json([
            'status' => true,
            'message' => 'Image deleted successfully',
        ]);
    }
}
