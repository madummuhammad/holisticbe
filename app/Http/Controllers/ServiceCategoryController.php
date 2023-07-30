<?php

namespace App\Http\Controllers;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
  public function list()
  {
    $ServiceCategory=ServiceCategory::orderBy('created_at','ASC')->get();
    return response()->json(['status'=>'success','data'=>$ServiceCategory]);
  }
}
