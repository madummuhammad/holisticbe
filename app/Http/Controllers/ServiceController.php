<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServicePartition;
use App\Models\Service;
use Validator;
class ServiceController extends Controller
{
    public function list(Request $request)
    {
        $service_category_id = $request->input('service_category_id');
        $city = $request->input('location');
        $type_service = $request->input('type_service');
        $user_id = $request->input('user_id');
        $limit = $request->input('limit');
        $query = Service::query();
        $currentDate = \Carbon\Carbon::now();

        $query->where(function ($q) use ($currentDate) {
            $q->where(function ($innerQ) use ($currentDate) {
                $innerQ->where('always_available', 0)
                ->whereDate('date_to', '>=', $currentDate);
            })->orWhere('always_available', 1);
        });

        if ($service_category_id) {
            $query->where('service_category_id', $service_category_id);
        }

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        if ($city) {
            $query->where('city', $city);
        }

        if ($type_service) {
            $query->where('type_service', $type_service);
        }

        if ($limit) {
            $services = $query->with('ratings')->paginate($limit);
        } else {
            $services = $query->with('ratings')->get();
        }
        foreach ($services as $service) {
            $totalStars = 0;
            $numberOfRatings = $service->ratings->count();

            foreach ($service->ratings as $rating) {
                $totalStars += $rating->star;
            }

            $averageRating = $numberOfRatings > 0 ? $totalStars / $numberOfRatings : 0;
            $averageRating = number_format($averageRating, 1);
            $service->average_rating = number_format($averageRating, 1);
        }
        return response()->json(['status' => 'success', 'data' => $services]);
    }


    public function filter(Request $request)
    {
        $type_service = $request->input('type_service');
        $category = $request->input('category');
        $city = $request->input('city');
        $ratings = $request->input('rating');

        $query = Service::with('ratings');
        $currentDate = \Carbon\Carbon::now();

        $currentDate = \Carbon\Carbon::now();

        $query->where(function ($q) use ($currentDate) {
            $q->where(function ($innerQ) use ($currentDate) {
                $innerQ->where('always_available', 0)
                ->whereDate('date_to', '>=', $currentDate);
            })->orWhere('always_available', 1);
        });

        if ($type_service) {
            $query->where('type_service', $type_service);
        }

        if ($category) {
            $categoryIds = array_map(function ($item) {
                return $item['id'];
            }, $category);

            $query->where(function ($query) use ($categoryIds) {
                $query->whereIn('service_category_id', $categoryIds)
                ->orWhereIn('service_sub_category_id', $categoryIds);
            });
        }
        if ($city) {
            $cityNames = array_map(function ($item) {
                return $item['name'];
            }, $city);

            $query->whereIn('city', $cityNames);
        }

        if ($ratings) {
            $totalStars = array_sum(array_column($ratings, 'star'));
            $averageRating = count($ratings) > 0 ? $totalStars / count($ratings) : 0;

            $query->whereHas('ratings', function ($query) use ($averageRating) {
                $query->where('star', '>=', $averageRating);
            });
        }

        $services = $query->paginate(20);

        foreach ($services as $service) {
            $totalStars = 0;
            $numberOfRatings = $service->ratings->count();

            foreach ($service->ratings as $rating) {
                $totalStars += $rating->star;
            }

            $averageRating = $numberOfRatings > 0 ? $totalStars / $numberOfRatings : 0;
            $averageRating = number_format($averageRating, 1);
            $service->average_rating = number_format($averageRating,1);
        }

        return response()->json(['status' => 'success', 'data' => $services]);
    }


    public function all(Request $request)
    {
        $service_category_id = $request->input('service_category_id');
        $city = $request->input('location');
        $type_service = $request->input('type_service');
        $user_id = $request->input('user_id');

        $query = Service::query();
        $currentDate = \Carbon\Carbon::now();

        $currentDate = \Carbon\Carbon::now();

        $query->where(function ($q) use ($currentDate) {
            $q->where(function ($innerQ) use ($currentDate) {
                $innerQ->where('always_available', 0)
                ->whereDate('date_to', '>=', $currentDate);
            })->orWhere('always_available', 1);
        });
        
        if ($service_category_id) {
            $query->where('service_category_id', $service_category_id);
        }

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        if ($city) {
            // return response()->json(['status' => 'adsfasdfsafd']);
            $query->where('city', $city);
        }

        if ($type_service) {
            $query->where('type_service', $type_service);
        }

        $services = $query->with('ratings')->paginate(20);
        foreach ($services as $service) {
            $totalStars = 0;
            $numberOfRatings = $service->ratings->count();

            foreach ($service->ratings as $rating) {
                $totalStars += $rating->star;
            }

            $averageRating = $numberOfRatings > 0 ? $totalStars / $numberOfRatings : 0;
            $averageRating = number_format($averageRating, 1);
            $service->average_rating = number_format($averageRating,1);
        }
        return response()->json(['status' => 'success', 'data' => $services]);
    }

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
            $service->average_rating=number_format($totalRatings/$rowRatings,1);

        }

        $totalStars = array_sum($starsCount);
        $starsPercentage = [];
        for ($i = 0; $i <= 4; $i++) {
            $percentage = $totalStars > 0 ? ($starsCount[$i] / $totalStars) * 100 : 0;
            $starsPercentage[$i + 1] = round($percentage, 2);
        }
        return response()->json(['status'=>'success','data'=>$service,'stars_count' => $starsPercentage]);
    }

    public function edit(Request $request)
    {
        $me = auth()->user();
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

        $serviceId = $request->input('id');
        $service = Service::find($serviceId);
        if (!$service) {
            return response()->json(['error' => 'Service not found']);
        }

        $service->update([
            'image' => $request->input('image'),
            'service_category_id' => $request->input('service_category_id'),
            'service_sub_category_id' => $request->input('service_sub_category_id'),
            'name' => $request->input('name'),
            'note' => $request->input('note'),
            'address' => $request->input('address'),
            'province' => $request->input('province'),
            'city' => $request->input('city'),
            'type_price' => $request->input('type_price'),
            'type_service' => $request->input('type_service'),
            'always_available' => $request->input('always_available'),
            'description' => $request->input('description'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ]);

        $servicePartitions = $request->input('service_partition');
        if ($servicePartitions) {
            foreach ($servicePartitions as $partition) {
                $servicePartitionId = $partition['id'];
                if (!is_null($servicePartitionId) && ServicePartition::find($servicePartitionId)) {
                    ServicePartition::where('id', $servicePartitionId)->update([
                        'user_id' => $me->id,
                        'service_id' => $serviceId,
                        'title' => $partition['title'],
                        'type_price' => $partition['type_price'],
                        'price' => $partition['price'],
                        'time_from' => $partition['time_from'],
                        'time_to' => $partition['time_to'],
                    ]);
                } else {
                    // return $serviceId;
                    $d=ServicePartition::create([
                        'user_id' => $me->id,
                        'service_id' => $serviceId,
                        'title' => $partition['title'],
                        'type_price' => $partition['type_price'],
                        'price' => $partition['price'],
                        'time_from' => $partition['time_from'],
                        'time_to' => $partition['time_to'],
                    ]);
                }
            }
        }

        // return $d;

        $servicePartitionCategory = $request->input('service_partition');
        if (is_null($servicePartitionCategory)) {
            ServicePartition::where('service_id', $serviceId)->delete();
        }

        return response()->json(['status' => 'success', 'message' => 'Service updated successfully']);
    }


    public function city(Request $request)
    {
        $limit = $request->input('limit');
        $keyword = $request->input('keyword');

        $query = Service::query();

        if ($keyword) {
            $query->where('city', 'LIKE', '%' . $keyword . '%');
        }

        if ($limit) {
            $cities = $query->limit($limit)->distinct()->select('city', 'province')->get();
        } else {
            $cities = $query->distinct()->select('city', 'province')->get();
        }

        $formattedCities = $cities->map(function ($city) {
            return ['name' => $city->city, 'province' => $city->province];
        });

        return response()->json(['status' => 'success', 'data' => $formattedCities]);
    }

    public function delete($id){
        Service::where('id',$id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Service deleted successfully']);
    }
}
