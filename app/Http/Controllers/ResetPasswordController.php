<?php

namespace App\Http\Controllers;

use App\Mail\ForgetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Throwable;

use Firebase\JWT\JWT;
use App\Models\PasswordReset;
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
            $getuser = User::where('email', $request->email)->first();
            $reset_password = rand(10, 20) . '$5%-W/x' . rand();
            $hash_passeord = Hash::make($reset_password);

            if (!$getuser) {
                return response(['Message' => 'No User Found']);
            }
            if ($getuser) {
                User::where('email', $getuser->email)->update([
                    'password' => $hash_passeord,
                ]);
                Mail::to($request->email)->send(new ForgetPasswordMail($request->email, $reset_password));
                return response(['Message' => "Updated Password has been send to your Email !"]);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }


    // this is another way to take input from user to update the new password 

    // public function resetPassword(Request $request, $token, $email)
    // {
    //     try {
    //         $request->validate([
    //             'password' => 'required|min:8'
    //         ]);

    //         $getuser = PasswordReset::where('token', $token)->where('email', $email)->where('expire', '1')->first();
    //         if ($getuser == null) {
    //             return response(['Message' => "The token is expire please Try again"]);
    //         }

    //         if ($getuser) {
    //             PasswordReset::where('email', $email)->where('token', $token)->update([
    //                 'expire' => 0,
    //             ]);

    //             User::where('email', $email)->update([
    //                 'password' => Hash::make($request->password),
    //             ]);
    //             return response(['Message' => "Password changed successfully!"]);
    //         }
    //     } catch (Throwable $e) {
    //         return $e->getMessage();
    //     }
    // }
}
