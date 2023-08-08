<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Validator;
class ProductController extends Controller
{
    public function list(Request $request)
    {
        $product_category_id = $request->input('product_category_id');
        $min_price = $request->input('min_price');
        $max_price = $request->input('max_price');

        $query = Product::query();

        if ($product_category_id) {
            $query->where('product_category_id', $product_category_id);
        }

        if ($min_price && $max_price) {
            $query->whereBetween('price', [$min_price, $max_price]);
        }

        $products = $query->with('user')->get();

        return response()->json(['status' => 'success', 'data' => $products]);
    }

    public function all(Request $request)
    {
        $product_category_id = $request->input('product_category_id');
        $min_price = $request->input('min_price');
        $max_price = $request->input('max_price');

        $query = Product::query();

        if ($product_category_id) {
            $query->where('product_category_id', $product_category_id);
        }

        if ($min_price && $max_price) {
            $query->whereBetween('price', [$min_price, $max_price]);
        }

        $products = $query->with('user')->paginate(20);

        return response()->json(['status' => 'success', 'data' => $products]);
    }

    public function filter(Request $request)
    {
        $category = $request->input('category');
        $min_price = $request->input('min');
        $max_price = $request->input('max');

        $query = Product::query();
        if ($category) {
            $categoryIds = array_map(function ($item) {
                return $item['id'];
            }, $category);

            $query->whereIn('product_category_id', $categoryIds);
            $query->orWhereIn('product_sub_category_id', $categoryIds);
        }

        if ($min_price && $max_price) {
            $query->whereBetween('price', [$min_price, $max_price]);
        }

        $products = $query->with('user')->paginate(20);
        return response()->json(['status' => 'success', 'data' => $products]);
    }

    public function new(){
        $product=Product::with('user')->limit(12)->orderBy('created_at','DESC')->get();
        $count=Product::count();
        return response()->json(['status'=>'success','data'=>$product,'count'=>$count]);
    }

    public function create(Request $request){
        $me = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'product_category_id' => 'required|exists:product_categories,id',
            // 'link' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'image' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $product = new Product();
        $product->user_id = $me->id;
        $product->name = $request->input('name');
        $product->product_category_id = $request->input('product_category_id');
        $product->product_sub_category_id = $request->input('product_sub_category_id');
        $product->link = $request->input('link');
        $product->price = $request->input('price');
        $product->description = $request->input('description');
        $product->image = $request->input('image');
        $product->save();

        return response()->json(['status' => 'success', 'message' => 'Product created successfully']);
    }

    public function edit(Request $request)
    {
        $me=auth()->user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'product_category_id' => 'required|exists:product_categories,id',
            'link' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'image' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $me = auth()->user();
        $service = Product::where('user_id', $me->id)->findOrFail($request->id);
        $service->update([
            'name'=>$request->input('name'),
            'product_category_id'=>$request->input('product_category_id'),
            'product_sub_category_id'=>$request->input('product_sub_category_id'),
            'link'=>$request->input('link'),
            'price'=>$request->input('price'),
            'description'=>$request->input('description'),
            'image'=>$request->input('image')
        ]);

        return response()->json(['status' => 'success', 'message' => 'Product updated successfully']);
    }

    public function detail()
    {
        $id=request('id');
        $service=Product::where('id',$id)->with('user')->first();
        return response()->json(['status'=>'success','data'=>$service]);
    }

    public function delete($id){
        Product::where('id',$id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Service deleted successfully']);
    }

    public function image_product(Request $request)
    {
        $url='http://localhost:8000/images/product/';

        if(env('APP_ENV')=='production'){
            $url='https://api.holisticstations.com/images/product/';
        }

        if(env('APP_ENV')=='development'){
            $url='https://api-dev.holisticstations.com/images/product/';
        }
        $img = $request->file('file');

        if ($img) {
            $filename = time() . '.' . $img->getClientOriginalExtension();
            $img->move(app()->basePath('public') . '/images/product/', $filename);
        } else {
            $filename = null;
        }

        return response()->json(['status' => 'success','image' => $url.$filename]);
    }
}
