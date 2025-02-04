<?php

use App\Models\Category;
use App\Models\ProductImage;

//Return category on dashboard
function getCategories()
{
    return Category::orderBy('name', 'ASC')->with('sub_category')->orderBy('id', 'DESC')->where('status', 1)->where('showHome', 'Yes')->get();
}

function getProductImage($poductId)
{
    return ProductImage::where('product_id', $poductId)->first();
}
