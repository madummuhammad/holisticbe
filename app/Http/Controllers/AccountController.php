<?php

namespace App\Http\Controllers;
use App\Models\Service;
use App\Models\User;
use App\Models\Product;
use App\Models\ServicePartition;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;
class AccountController extends Controller
{
    public function service()
    {
        $me = auth()->user();

        $services = Service::where('user_id', $me->id)
        ->with('ratings')
        ->orderBy('created_at', 'ASC')
        ->get();

        foreach ($services as $service) {
            $totalRatings = count($service->ratings);
            $totalStars = 0;

            foreach ($service->ratings as $rating) {
                $totalStars += $rating->star;
            }

            $averageRating = ($totalRatings > 0) ? $totalStars / $totalRatings : 0;
            $service->average_rating = number_format($averageRating,1);
        }

        return response()->json(['status' => 'success', 'data' => $services]);
    }

    public function product()
    {
     $me = auth()->user();
     $products=Product::where("user_id",$me->id)->get(); 
     return response()->json(['status' => 'success', 'data' => $products]);
 }

 public function create_service(Request $request){

    $price=preg_replace('/[^0-9]/', '', $request->input('price'));

    $me=auth()->user();
    $validator = Validator::make($request->all(), [
        // 'image' => 'required',
        'service_category_id' => 'required|exists:service_categories,id',
        // 'service_sub_category_id' => 'required|exists:service_categories,id',
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'province' => 'required|string|max:255',
        'city' => 'required|string|max:255',
        'type_price'=>'required',
        'phone'=>'required',
        'type_service'=>'required',
        'description' => 'required|string',
        // 'note' => 'required|string',
        'date_from' => 'required',
        'date_to' => 'required',
        'from' => 'required',
        'to' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()]);
    }

    $service = new Service();
    $service->user_id = $me->id;
    $service->price = $price;
    $service->phone = $request->input('phone');
    $service->service_category_id = $request->input('service_category_id');
    $service->service_sub_category_id = $request->input('service_sub_category_id');
    $service->name = $request->input('name');
    $service->note = $request->input('note');
    $service->date_from = $request->input('date_from');
    $service->date_to = $request->input('date_to');
    $service->from = $request->input('from');
    $service->to = $request->input('to');
    $service->province = $request->input('province');
    $service->always_available = $request->input('always_available');
    $service->city = $request->input('city');
    $service->address = $request->input('address');
    $service->type_price = $request->input('type_price');
    $service->type_service = $request->input('type_service');
    $service->description = $request->input('description');
    $service->image = $request->input('image');

    $service->save();

    $partitionServices = $request->input('partition_service');
    if ($partitionServices && is_array($partitionServices)) {
        foreach ($partitionServices as $partition) {
            $servicePartition = new ServicePartition();
            $servicePartition->user_id = $me->id;
            $servicePartition->service_id = $service->id; // The ID of the newly created service
            $servicePartition->title = $partition['title'];
            $servicePartition->type_price = $partition['type_price'];
            $servicePartition->price = preg_replace('/[^0-9]/', '', $partition['price']);
            $servicePartition->time_from = $partition['from'];
            $servicePartition->time_to = $partition['to'];
            $servicePartition->save();
        }
    } else {
        $servicePartition = new ServicePartition();
        $servicePartition->user_id = $me->id;
        $servicePartition->service_id = $service->id;
        $servicePartition->title = $request->input('name');
        $servicePartition->type_price = $request->input('type_price');
        $servicePartition->price = $price;
        $servicePartition->time_from = $request->input('from');
        $servicePartition->time_to = $request->input('to');
        $servicePartition->save();
    }


    return response()->json(['status'=>'success','message' => 'Service created successfully']);
}

public function image_service(Request $request)
{
    $url='http://localhost:8000/images/service/';

    if(env('APP_ENV')=='production'){
        $url='https://api.holisticstations.com/images/service/';
    }

    if(env('APP_ENV')=='development'){
        $url='https://api-dev.holisticstations.com/images/service/';
    }
    $img = $request->file('file');

    if ($img) {
        $filename = time() . '.' . $img->getClientOriginalExtension();
        $img->move(app()->basePath('public') . '/images/service/', $filename);
    } else {
        $filename = null;
    }

    return response()->json(['status' => 'success','image' => $url.$filename]);
}

public function edit_profile()
{
    $me=auth()->user();
    $image=request('image');
    User::where('id',$me->id)->update(['image'=>$image]);

    return response()->json(['status' => 'success','message' => 'Successfully changed profile image']);
}

public function image_profile(Request $request)
{
    $me = auth()->user();
    $url = 'http://localhost:8000/images/profile/';

    if (env('APP_ENV') == 'production') {
        $url = 'https://api.holisticstations.com/images/profile/';
    }

    if (env('APP_ENV') == 'development') {
        $url = 'https://api-dev.holisticstations.com/images/profile/';
    }

    $img = $request->file('file');
    $resize = Image::make($img)->fit(180, 280);

    // Pemangkasan di atas, menggunakan koordinat x yang tetap di tengah
    $x = ($resize->width() - 180) / 2;
    $y = 0;
    $cropWidth = 180;
    $cropHeight = 180;

    $resize->crop($cropWidth, $cropHeight, $x, $y);

    $filename = time() . '.' . $img->getClientOriginalExtension();

    if ($img) {
        $resize->save(app()->basePath('public') . '/images/profile/' . $filename);

        return response()->json(['status' => 'success', 'image' => $url . $filename]);
    } else {
        return response()->json(['status' => 'error', 'message' => 'Image upload failed.']);
    }
}


}
