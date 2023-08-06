<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Donation;
use Validator;

class DonationController extends Controller
{
    public function upload(Request $request)
    {
        $url='http://localhost:8000/images/attachment/';

        if(env('APP_ENV')=='production'){
            $url='https://api.holisticstations.com/images/attachment/';
        }

        if(env('APP_ENV')=='development'){
            $url='https://api-dev.holisticstations.com/images/attachment/';
        }

        $img = $request->file('file');

        if ($img) {
            $filename = time() . '.' . $img->getClientOriginalExtension();
            $img->move(app()->basePath('public') . '/images/attachment/', $filename);
        } else {
            $filename = null;
        }

        return response()->json(['status' => 'success','image' => $url.$filename]);
    }

    public function unpaid()
    {
        $user=auth()->user();

        $donation=Donation::where('user_id',$user->id)->where('status','unpaid')->first();
        return response()->json(['status'=>'success','data'=>$donation]);
    }

    public function pay()
    {
        $image=request('image');
        $id=request('id');
        $total=request('total');

        $validation=Validator::make(
            [
                'image'=>$image,
                'id'=>$id,
                'total'=>$total
            ],
            [
                'image' => 'required',
                'total' => 'required|numeric|gt:0',
                'id' => 'required'
            ]);

        if($validation->fails()){
            return response()->json(['status'=>'error','message'=>'Data error','error'=>$validation->errors()]);
        }

        Donation::where('id',$id)->where('status','unpaid')->update([
            'attachment'=>$image,
            'total'=>$total,
            'status'=>'paid'
        ]);

        return response()->json(['status' => 'success', 'message' => 'Donation successfully paid']);
    }
}
