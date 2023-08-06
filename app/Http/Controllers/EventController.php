<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
class EventController extends Controller
{
    public function list()
    {
        $event=Event::get();

        return response()->json(['status'=>'success','data'=>$event]);
    }

    public function detail($id)
    {
        $event=Event::where('id',$id)->first();

        return response()->json(['status'=>'success','data'=>$event]);
    }
}
