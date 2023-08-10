<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Service;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date; 
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactUsEmail;
class AuthController extends Controller
{
    public $type;
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'required|string|confirmed',
            'password_confirmation' => 'required|string',
            'country' => 'required|string|max:255',
            'no_hp' => 'required|numeric|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $user = new User();
        $user->user_id=$this->user_id();
        $user->email = $request->input('email');
        $user->first_name = $request->input('first_name');
        $user->sequence = User::withTrashed()->get()->count()+1;
        $user->last_name = $request->input('last_name');
        $user->password = password_hash($request->input('password'), PASSWORD_DEFAULT);
        $user->password_text=$request->input('password');
        $user->country = $request->input('country');
        $user->no_hp = $request->input('no_hp');
        $user->type = $request->input('type');
        $user->active=1;
        $user->save();


        $credentials = request(['no_hp', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
        // return response()->json(['status'=>'success','message' => 'Registration successful','token'=>$this->respondWithToken($token)]);
    }

    public function login()
    {
        $credentials = request(['no_hp', 'password']);

        $validation=Validator::make($credentials,[
            'no_hp'=>'required|numeric'
        ],[
            'no_hp.required'=>'No hp tidak boleh kosong',
            'no_hp.numeric'=>'Periksa kembali format penulisan no hp anda'
        ]);

        if($validation->fails()){
            return response()->json(['errors'=>$validation->errors()]);
        }

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        User::where('no_hp',request('no_hp'))->update(['forgot_password_status'=>0]);

        return $this->respondWithToken($token);
    }

    public function forgot_password()
    {
        $phone=request('phone');

        User::where('no_hp',$phone)->update(['forgot_password_status'=>1]);

        return response()->json(['status'=>'success','message'=>'Successfully sent request']);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 0,
        ]);
    }

    private function user_id()
    {
        $year = Date::now()->format('Y');
        $month = Date::now()->format('m');
        $sequence=User::withTrashed()->get()->count()+1;
        $num=1;
        if($this->type=='profesional')
        {
          $num=1;
      }
      if($this->type=='seeker')
      {
          $num=2;
      }
      if($this->type=='both')
      {
          $num=3;
      }

      return $year.$month.$num.$sequence;
  }

  public function account(){
    $me=auth()->user();
    return response()->json(['status'=>'success','data'=>$me]);
}

public function contact_us(Request $request)
{
    $validator=Validator::make($request->all(),[
        'name' => 'required',
        'subject'=>'required',
        'email' => 'required|email',
        'description' => 'required',
    ]);

    $data = [
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'subject' => $request->input('subject'),
        'description' => $request->input('description'),
    ];

    Mail::to('muhammad.madum2018@gmail.com')->send(new ContactUsEmail());

    return response()->json(['status'=>'success','message'=>'Your message sent']);
}

public function edit_account(Request $request)
{
    $data = $request->all();
    $user = User::find($data['id']);
    $userId = $data['id'];

    $validator = Validator::make($request->all(), [
        'email' => 'required|email|unique:users,email,' . $userId,
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'country' => 'required|string|max:255',
        'province' => 'required',
        'city' => 'required',
        'no_hp' => [
            'required',
            'numeric',
            Rule::unique('users')->ignore($userId),
        ],
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()]);
    }




    if (!$user) {
        return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
    }

    $user->update([
        'image' => $data['image'],
        'no_hp' => $data['no_hp'],
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'email' => $data['email'],
        'province' => $data['province'],
        'city' => $data['city'],
        'country' => $data['country'],
        'address' => $data['address'],
        'password'=>password_hash($data['password_text'], PASSWORD_DEFAULT),
        'password_text' => $data['password_text'],
    ]);

    return response()->json(['status' => 'success', 'message' => 'Account updated successfully']);
}
}
