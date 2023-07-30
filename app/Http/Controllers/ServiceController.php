<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Validator;
class ServiceController extends Controller
{
    public function detail()
    {
        $id=request('id');
        $service=Service::where('id',$id)->with('ratings','user','service_partition')->first();

        $rowRatings = count($service->ratings);

        $totalRatings=0;
        $starsCount = [0, 0, 0, 0, 0];
        foreach ($service->ratings as $rating) {
            $totalRatings += $rating->star;
            if ($rating->star >= 5) {
                $starsCount[4]++;
            } elseif ($rating->star >= 4) {
                $starsCount[3]++;
            } elseif ($rating->star >= 3) {
                $starsCount[2]++;
            } elseif ($rating->star >= 2) {
                $starsCount[1]++;
            } else {
                $starsCount[0]++;
            }
        }

        $service->average_rating=0;
        if($totalRatings!==0){
            $service->average_rating=$totalRatings/$rowRatings;
        }

        $totalStars = array_sum($starsCount);
        $starsPercentage = [];
        for ($i = 0; $i <= 4; $i++) {
            $percentage = $totalStars > 0 ? ($starsCount[$i] / $totalStars) * 100 : 0;
            $starsPercentage[$i + 1] = round($percentage, 2);
        }
        return response()->json(['status'=>'success','data'=>$service,'stars_count' => $starsPercentage]);
    }

    public function edit(Request $request){
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
            'date' => 'required',
            'from' => 'required',
            'to' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $me = auth()->user();
        $service = Service::where('user_id', $me->id)->findOrFail($request->id);
        $service->update($request->all());

        return response()->json(['status' => 'success', 'message' => 'Service updated successfully']);
    }

    public function delete($id){
        Service::where('id',$id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Service deleted successfully']);
    }
}
