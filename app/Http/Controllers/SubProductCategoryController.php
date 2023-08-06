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

        $query = ProductCategory::where('level','parent')->orderBy('created_at', 'ASC');

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
