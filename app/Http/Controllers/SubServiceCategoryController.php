<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceCategory;
class SubServiceCategoryController extends Controller
{
    public function list(Request $request)
    {
        $limit = $request->input('limit');
        $keyword = $request->input('keyword');
        $parent_id = $request->input('parent_id');

        $query = ServiceCategory::where('level','sub')->orderBy('created_at', 'ASC');
        if ($parent_id) {
          $query->where('parent_id', $parent_id);
      }
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
