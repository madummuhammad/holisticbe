<?php

namespace App\Http\Controllers;
use App\Models\Service;
use App\Models\Product;
use App\Models\ServicePartition;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\File;
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
            $service->average_rating = $averageRating;
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
    $me=auth()->user();
    $validator = Validator::make($request->all(), [
        'image' => 'required',
        'service_category_id' => 'required|exists:service_categories,id',
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'province' => 'required|string|max:255',
        'city' => 'required|string|max:255',
        'type_price'=>'required',
        'description' => 'required|string',
        'note' => 'required|string',
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
    $service->price = $request->input('price');
    $service->phone = $request->input('phone');
    $service->service_category_id = $request->input('service_category_id');
    $service->name = $request->input('name');
    $service->date_from = $request->input('date_from');
    $service->date_to = $request->input('date_to');
    $service->from = $request->input('from');
    $service->to = $request->input('to');
    $service->province = $request->input('province');
    $service->city = $request->input('city');
    $service->address = $request->input('address');
    $service->type_price = $request->input('type_price');
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
            $servicePartition->price = $partition['price'];
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
        $servicePartition->price = $request->input('price');
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
    $img = $request->file('file');

    if ($img) {
        $filename = time() . '.' . $img->getClientOriginalExtension();
        $img->move(app()->basePath('public') . '/images/service/', $filename);
    } else {
        $filename = null;
    }

    return response()->json(['status' => 'success','image' => $url.$filename]);
}
}
