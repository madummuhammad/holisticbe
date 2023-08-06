<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Book;
use App\Models\Schedule;
use App\Models\Donation;
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
   $schedule=Schedule::where('service_id',$serviceData['service_id'])
   ->whereDate('date',$date)
   ->where('time_from',$serviceData['time_from'])
   ->where('time_to',$serviceData['time_to'])
   ->where('status_professional','proses')
   ->orWhere('status_seeker','proses')
   ->first();



   $book=Book::where('by_user_id',$me->id)->where('status','new')->where('service_partition_id',$serviceData['id'])->whereDate('date',$date)->first();
   if($book){
     return response()->json(['status'=>'error','message' => 'You have booked this service at that date']);
   }

   if($schedule){
     return response()->json(['status'=>'error','message' => 'Service unavailable']);
   }

   $check=Book::where('by_user_id',$me->id)->exists();
   if($check){
    $donation=Donation::where('user_id',$me->id)->where('status','unpaid')->exists();
    if($donation){
      return response()->json(['status'=>'error','message'=> 'You havent pay donation']);
    }
  }

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

  $book_create=Book::create($bookData);

  Donation::create([
    'user_id'=>$me->id,
    'book_id'=>$book_create->id,
    'total'=>0,
    'attachment'=>null,
    'status'=>'unpaid'
  ]);

  return response()->json(['status'=>'success','message' => 'Data has been successfully saved']);
}

public function check()
{
  $me = auth()->user();
  $id=request('id');
  $date=request('date');

  $book=Book::where('by_user_id',$me->id)->where('status','new')->where('service_partition_id',$id)->whereDate('date',$date)->first();
  if($book){
   return response()->json(['status'=>'error','message' => 'You have booked this service at that date']);
 } else {
   return response()->json(['status'=>'success','message' => 'Service available']);
 }
}
}
