<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
class UserController extends Controller
{
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
            $user->average_rating = $averageRating;
        }
        return response()->json(['status'=>'success','data'=>$users]);
    }
}
