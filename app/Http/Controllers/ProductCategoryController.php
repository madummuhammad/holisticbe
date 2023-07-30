<?php

namespace App\Http\Controllers;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
public function list()
{
    $ProductCategory=ProductCategory::orderBy('created_at','ASC')->get();
    return response()->json(['status'=>'success','data'=>$ProductCategory]);
}
}
