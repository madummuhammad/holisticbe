<?php

namespace App\Http\Controllers;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
  public function list(Request $request)
  {
    $limit = $request->input('limit');
    $keyword = $request->input('keyword');

    $query = ServiceCategory::where('level','parent')->orderBy('created_at', 'ASC');

    if ($keyword) {
      $query->where('name', 'LIKE', '%' . $keyword . '%');
    }

    if ($limit) {
      $ServiceCategory = $query->limit($limit)->get();
    } else {
      $ServiceCategory = $query->get();
    }

    return response()->json(['status' => 'success', 'data' => $ServiceCategory]);
  }

  public function all(Request $request)
  {
    $limit = $request->input('limit');
    $keyword = $request->input('keyword');

    $query = ServiceCategory::where('level', 'parent')->with(['child' => function ($query) {
      $query->orderBy('name', 'ASC'); 
    }])->orderBy('created_at', 'ASC');

    if ($keyword) {
      $query->where('name', 'LIKE', '%' . $keyword . '%');
    }

    if ($limit) {
      $ServiceCategory = $query->limit($limit)->get();
    } else {
      $ServiceCategory = $query->get();
    }

    return response()->json(['status' => 'success', 'data' => $ServiceCategory]);
  }

}
