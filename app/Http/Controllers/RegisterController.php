<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Validator;
use App\User;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $all_data = $request->all();
        $validator = $this->validateit($all_data);

        if($validator->fails()){
            return $this->fail_msg($validator);
        }

        $user_by_username = User::where('username', $all_data['username'])->first();
        if($user_by_username)
            return $this->fail_msg_username();

        $user_by_email = User::where('email', $all_data['email'])->first();
        if($user_by_email)
            return $this->fail_msg_email();

        $input = $all_data;
        $input['uuid'] = (string) Str::uuid();
        $input['password'] = Hash::make($input['password']);
        $input['last_address'] = $request->ip();
        $user = User::create($input);
      
        /**Take note of this: Your user authentication access token is generated here **/
        $data['token'] =  $user->createToken('MyApp')->accessToken;
        $data['username'] =  $user->username;

        return response(['data' => $data, 'message' => 'Account created successfully!', 'status' => true]);
    }

    public function validateit($all_data)
    {
        $validator = Validator::make($all_data, [
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]);

        return $validator;
    }

    public function fail_msg($validator)
    {
        return response([
            'message' => 'Validation errors',
            'errors' =>  $validator->errors(),
            'status' => false], 422);
    }

    public function fail_msg_username()
    {
        return response([
            'message' => 'Username exist',
            'status' => false], 422);
    }

    public function fail_msg_email()
    {
        return response([
            'message' => 'Email exist',
            'status' => false], 422);
    }

}