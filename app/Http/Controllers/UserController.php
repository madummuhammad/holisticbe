<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Service;
class UserController extends Controller
{
    public function detail(Request $request)
    {
        $id = $request->input('id');
        $user = User::where('id', $id)->first();

        $user->service = null;
        $service = Service::where('user_id', $user->id)->with('ratings')->get();

        $requestDate = $request->input('date');

        if ($requestDate) {
            $filteredServices = $service->filter(function ($serviceItem) use ($requestDate) {
                $serviceDateFrom = $serviceItem->date_from;
                $serviceDateTo = $serviceItem->date_to;

                return $this->isDateInRange($requestDate, $serviceDateFrom, $serviceDateTo);
        })->values(); // Tambahkan metode values() untuk mengatur ulang indeks array.

            foreach ($filteredServices as $filteredService) {
                $totalStars = 0;
                $numberOfRatings = $filteredService->ratings->count();

                foreach ($filteredService->ratings as $rating) {
                    $totalStars += $rating->star;
                }

                $averageRating = $numberOfRatings > 0 ? $totalStars / $numberOfRatings : 0;
                $filteredService->average_rating = number_format($averageRating,1);
            }

            $user->service = $filteredServices;
        } else {
            foreach ($service as $serviceItem) {
                $totalStars = 0;
                $numberOfRatings = $serviceItem->ratings->count();

                foreach ($serviceItem->ratings as $rating) {
                    $totalStars += $rating->star;
                }

                $averageRating = $numberOfRatings > 0 ? $totalStars / $numberOfRatings : 0;
                $serviceItem->average_rating = number_format($averageRating,1);
            }

            $user->service = $service;
        }

        return response()->json(['status' => 'success', 'data' => $user]);
    }

    private function isDateInRange($date, $dateFrom, $dateTo)
    {
        $date = \Carbon\Carbon::parse($date);
        $dateFrom = \Carbon\Carbon::parse($dateFrom);
        $dateTo = \Carbon\Carbon::parse($dateTo);

        return $date->between($dateFrom, $dateTo, true);
    }



    public function new()
    {
        $users=User::where('type','professional')->with('ratings')->limit(12)->orderBy('created_at','DESC')->get();

        foreach ($users as $user) {
            $totalStars = 0;
            $numberOfRatings = $user->ratings->count();

            foreach ($user->ratings as $rating) {
                $totalStars += $rating->star;
            }

            $averageRating = $numberOfRatings > 0 ? $totalStars / $numberOfRatings : 0;
            $user->average_rating = number_format($averageRating,1);
        }
        $count=User::where('type','professional')->count();
        return response()->json(['status'=>'success','data'=>$users,'count'=>$count]);
    }

    public function all()
    {
        $users=User::where('type','professional')->with('ratings')->limit(12)->orderBy('created_at','DESC')->paginate();

        foreach ($users as $user) {
            $totalStars = 0;
            $numberOfRatings = $user->ratings->count();

            foreach ($user->ratings as $rating) {
                $totalStars += $rating->star;
            }

            $averageRating = $numberOfRatings > 0 ? $totalStars / $numberOfRatings : 0;
            $user->average_rating = number_format($averageRating,1);
        }
        return response()->json(['status'=>'success','data'=>$users]);
    }

    public function favorite()
    {
        $users = User::where('type', 'professional')->with('ratings')->limit(12)->get();

        $filteredUsers = collect();

        foreach ($users as $user) {
            $totalStars = 0;
            $numberOfRatings = $user->ratings->count();

            if ($numberOfRatings > 0) {
                foreach ($user->ratings as $rating) {
                    $totalStars += $rating->star;
                }

                $averageRating = $totalStars / $numberOfRatings;
                $user->average_rating = number_format($averageRating, 1);

                $filteredUsers->push($user);
            }
        }

        $sortedUsers = $filteredUsers->sortByDesc(function ($user) {
            return $user->ratings->count();
        });

        return response()->json(['status' => 'success', 'data' => $sortedUsers]);
    }

    public function filter(Request $request)
    {
        $city = $request->input('city');
        $ratings = $request->input('rating');

        $query = User::where('type','professional')->with('ratings');

        if ($city) {
            $cityNames = array_map(function ($item) {
                return $item['name'];
            }, $city);

            $query->whereIn('city', $cityNames);
        }

        if ($ratings) {
            $totalStars = array_sum(array_column($ratings, 'star'));
            return $averageRating = count($ratings) > 0 ? $totalStars / count($ratings) : 0;

            $query->whereHas('ratings', function ($query) use ($averageRating) {
                $query->where('star', '>=', $averageRating);
            });
        }

        $users = $query->paginate(20);
        foreach ($users as $user) {
            $totalStars = 0;
            $numberOfRatings = $user->ratings->count();

            foreach ($user->ratings as $rating) {
                $totalStars += $rating->star;
            }

            $averageRating = $numberOfRatings > 0 ? $totalStars / $numberOfRatings : 0;
            $user->average_rating = number_format($averageRating,1);
        }
        return response()->json(['status' => 'success', 'data' => $users]);
    }

}
