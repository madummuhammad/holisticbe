<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Rating;
use App\Models\RatingUser;
use App\Models\Donation;
use App\Models\Schedule;
class ScheduleController extends Controller
{
    public function list()
    {
        $status=request('status');
        $user=auth()->user();
        if($user->type=='professional'){
            $book=Book::with('service')->where('status',$status)->where('user_id',$user->id)->orderBy('date','ASC')->get();
        } else{
            $book=Book::with('service')->where('status',$status)->where('by_user_id',$user->id)->orderBy('date','ASC')->get();
        }
        return response()->json(['status'=>'success','data'=>$book]);
    }

    public function upcoming()
    {
        $status=request('status');
        $user=auth()->user();
        if($user->type=='professional'){
            $schedule=Schedule::where('user_id',$user->id)->with('service')->where('status_professional','proses')->orderBy('date','ASC')->get();
        } else {
            $schedule=Schedule::where('by_user_id',$user->id)->with('service')->where('status_seeker','proses')->orderBy('date','ASC')->get();
        }
        return response()->json(['status'=>'success','data'=>$schedule]);
    }

    public function complete()
    {
        $status=request('status');
        $user=auth()->user();
        if ($user->type == 'professional') {
            $schedule = Schedule::with(['rating', 'rating_user' => function ($query) {
                    $query->withTrashed(); // Sertakan rating_user yang sudah dihapus secara lembut (soft-deleted)
                }])
            ->where('user_id', $user->id)
            ->with('service')
            ->where('status_professional', 'done')
            ->orderBy('date', 'ASC')
            ->get();
        } else {
            $schedule = Schedule::with(['rating', 'rating_user' => function ($query) {
                    $query->withTrashed(); // Sertakan rating_user yang sudah dihapus secara lembut (soft-deleted)
                }])
            ->where('by_user_id', $user->id)
            ->with('service')
            ->where('status_seeker', 'done')
            ->orderBy('date', 'ASC')
            ->get();
        }
        return response()->json(['status'=>'success','data'=>$schedule]);
    }

    public function accept()
    {
        $user=auth()->user();
        $id=request('id');
        $book=Book::where('id',$id)->first();

        $check=Schedule::where('user_id',$user->id)->exists();
        if($check){
            $donation=Donation::where('user_id',$user->id)->where('status','unpaid')->exists();
            if($donation){
                return response()->json(['status'=>'error','message'=> 'You havent payed donation']);
            }
        }

        $schedule=Schedule::create([
            'book_id'=>$book->id,
            'rating_id'=>null,
            'date'=>$book->date,
            'time_from'=>$book->time_from,
            'time_to'=>$book->time_to,
            'service_id'=>$book->service_id,
            'user_id'=>$user->id,
            'by_user_id'=>$book->by_user_id
        ]);

        Donation::create([
            'user_id'=>$user->id,
            'schedule_id'=>$schedule->id,
            'total'=>0,
            'attachment'=>null,
            'status'=>'unpaid'
        ]);



        Book::where('id',$id)->where('status','new')->update(['status'=>'accepted']);

        return response()->json(['status'=>'success','message'=> 'Service accepted']);
    }

    public function reject()
    {
        $user=auth()->user();
        $id=request('id');
        Book::where('id',$id)->where('status','new')->update(['status'=>'rejected']);
        return response()->json(['status'=>'success','message'=> 'Service rejected']);
    }

    public function finish()
    {
        $user=auth()->user();
        $id=request('id');
        if($user->type=='professional'){
            Schedule::where('id',$id)->where('status_professional','proses')->update(['status_professional'=>'done']);
        } else {
            Schedule::where('id',$id)->where('status_seeker','proses')->update(['status_seeker'=>'done']);
        }
        return response()->json(['status'=>'success','message'=> 'Service finished']);
    }

    public function rate()
    {
        $user=auth()->user();
        $id=request('id');
        $schedule=Schedule::where('id',$id)->first();
        $review=request('review');


        if($user->type=='seeker'){       
            if(request('star')==0){
                return response()->json(['status'=>'error','message'=> 'You havent rate yet']);
            }
            $rating=Rating::create([
                'star'=>request('star'),
                'review'=>$review,
                'service_id'=>$schedule->service_id,
                'user_id'=>$schedule->user_id,
                'by_user_id'=>$user->id
            ]);
            Schedule::where('id',$id)->update(['rating_id'=>$rating->id]);
        }

        $schedule=Schedule::where('id',$id)->first();

        if(request('rate_for')){
            RatingUser::create([
                'rate'=>request('rate_for'),
                'service_id'=>$schedule->service_id,
                'user_id'=>$schedule->user_id,
                'schedule_id'=>$schedule->id,
                'by_user_id'=>$user->id
            ]);
        }


        return response()->json(['status'=>'success','message'=> 'Rating Successfully Created']);
    }
}
