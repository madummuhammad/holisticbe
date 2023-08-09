<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
class SettingController extends Controller
{
    public function index()
    {
        $setting=Setting::first();
        return response()->json(['status'=>'success','data'=>$setting]);
    }
}
