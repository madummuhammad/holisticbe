<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Question;
class SettingController extends Controller
{
    public function index()
    {
        $setting=Setting::first();
        return response()->json(['status'=>'success','data'=>$setting]);
    }

    public function faq()
    {
        $faq=Question::with('answer')->orderBy('created_at','ASC')->get();
        return response()->json(['status'=>'success','data'=>$faq]);
    }
}
