<?php

namespace App\Http\Controllers;

use App\Mail\ForgetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Throwable;

use Firebase\JWT\JWT;
use App\Models\PasswordReset;
use App\Services\createToken;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    //creating a token 
    function createToken($data)
    {
        $key = "SocialCamp";
        $payload = array(
            "iss" => "http://127.0.0.1:8000",
            "aud" => "http://127.0.0.1:8000/api",
            "iat" => time(),
            "nbf" => 1357000000,
            "id" => $data,
            'token_type' => 'bearer'
        );

        $token = JWT::encode($payload, $key, 'HS256');

        return $token;
    }

    public function forgetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);
            $forgetpass_token = (new createToken)->createToken($request->email);
            $reset_url = 'http://127.0.0.1:8000/api/reset_password/' . $forgetpass_token . '/' . $request->email;

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response(['Message' => 'No User Found']);
            }
            //create new User in DB
            $user = PasswordReset::create([
                'token' => $forgetpass_token,
                'email' => $request->email,
                'expire' => 1
            ]);

            //send Email by using php artisan make:mail
            Mail::to($request->email)->send(new ForgetPasswordMail($reset_url, $user->email));

            return response(['Message' => "Reset password request sent successfully!"]);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function resetPassword(Request $request, $token, $email)
    {
        try {
            $request->validate([
                'password' => 'required|min:8'
            ]);

            $getuser = PasswordReset::where('token', $token)->where('email', $email)->where('expire' , '1')->first();
            // dd($getuser);
            if($getuser == null){
                return response(['Message' => "The token is expire please Try again"]);
            }

            if ($getuser) {
                PasswordReset::where('email', $email)->where('token', $token)->update([
                    'expire' => 0,
                ]);

                User::where('email', $email)->update([
                    'password' => Hash::make($request->password),
                ]);
                return response(['Message' => "Password changed successfully!"]);
            }
            
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}
