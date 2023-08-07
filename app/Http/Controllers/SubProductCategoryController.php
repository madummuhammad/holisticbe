<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
class SubProductCategoryController extends Controller
{
    public function list(Request $request)
    {
        $limit = $request->input('limit');
        $keyword = $request->input('keyword');
        $parent_id = $request->input('parent_id');

        $query = ProductCategory::where('level','sub')->orderBy('created_at', 'ASC');

        if ($parent_id) {
          $query->where('parent_id', $parent_id);
      }
      
      if ($keyword) {
          $query->where('name', 'LIKE', '%' . $keyword . '%');
      }

      if ($limit) {
        $ProductCategory = $query->limit($limit)->get();
    } else {
        $ProductCategory = $query->get();
    }
    

    return response()->json(['status' => 'success', 'data' => $ProductCategory]);
}
}
