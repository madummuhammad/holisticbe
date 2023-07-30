<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SLider;
class SliderController extends Controller
{
    public function homepage()
    {
        $slider=Slider::where('type','homepage')->orderBy('created_at','ASC')->get();
        return response()->json(['status'=>'success','data'=>$slider]);
    }

    public function product()
    {
        $slider=Slider::where('type','product')->orderBy('created_at','ASC')->get();
        return response()->json(['status'=>'success','data'=>$slider]);
    }
}
