<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Book;
class BookController extends Controller
{
 public function create(Request $request)
 {
    $me = auth()->user();
    $validator = Validator::make($request->all(), [
     'service.user_id' => 'required',
     'service.service_id' => 'required',
     'service.title' => 'required',
     'service.type_price' => 'required',
     'service.time_from' => 'required',
     'service.time_to' => 'required',
     'service.created_at' => 'required',
     'service.updated_at' => 'required',
     'date' => 'required|date',
  ]);

    if ($validator->fails()) {
     return response()->json(['errors' => $validator->errors()]);
  }

  $serviceData = $request->input('service');
  $date = $request->input('date');

  $bookData = [
     'service_id' => $serviceData['service_id'],
     'service_partition_id' => $serviceData['id'],
     'user_id' => $serviceData['user_id'],
     'by_user_id' => $me->id, 
     'date' => $date,
     'time_from' => $serviceData['time_from'],
     'time_to' => $serviceData['time_to'],
     'type_price' => $serviceData['type_price'],
     'total' => $serviceData['price']
  ];

  Book::create($bookData);

  return response()->json(['message' => 'Data has been successfully saved']);
}

public function check()
{
 $me = auth()->user();
 $id=request('id');
 $date=request('date');

 $book=Book::where('by_user_id',$me->id)->where('status','proses')->where('service_partition_id',$id)->whereDate('date',$date)->first();
 if($book){
    return response()->json(['status'=>'error','message' => 'You have booked this service at that date']);
 } else {
    return response()->json(['status'=>'success','message' => 'Service available']);
 }
}
}
