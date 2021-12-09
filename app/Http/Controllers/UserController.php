<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Http\Request;
use App\Models\Token;
use App\Models\User;
use App\Services\createToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Throwable;

use Firebase\JWT\JWT;
use App\Mail\Sendmail;

class UserController extends Controller
{
    /**
     * This Function is creating a Token for the authenticated user.
     *
     */

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


    /**
     * Registering a new user.
     */

    public function register(RegisterUserRequest $request)
    {
        try {
            // Validate the user inputs
            $request->validated();
            //create a link to varify email.       
            $verification_token = (new createToken)->createToken($request->email);
            $url = "http://127.0.0.1:8000/api/emailVerify/" . $verification_token . '/' . $request->email;

            if ($image = $request->file('profile_pic')) {
                //make a path to store image
                $destinationPath = 'profile/';
                //change the image name for no duplication of same name
                $profileImage = date('YmdHis') . "." . $image->getClientOriginalName();
                //store file in a provided path
                $image->move($destinationPath, $profileImage);
            }

            //create new User in DB
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'age' => $request->age,
                'verification_token' => $url,
                'profile_pic' => $profileImage,
                'password' => Hash::make($request->password),
            ]);

            //send Email by using php artisan make:mail
            Mail::to($request->email)->send(new Sendmail($url, $user->email));

            //message on Register
            return response([
                'Status' => '200',
                'message' => 'Thanks, you have successfully signup',
                "Mail" => "Email Sended Successfully",
                'user' => $user
            ], 200);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    //create function to verify the email
    function EmailVerify($token, $email)
    {
        try {
            $emailVerify = User::where('email', $email)->first();

            if ($emailVerify->email_verified_at != null) {
                return response([
                    'message' => 'Already Varified'
                ]);
            } elseif ($emailVerify) {
                $emailVerify->email_verified_at = date('Y-m-d h:i:s');
                $emailVerify->save();
                return response([
                    'message' => 'Thankyou Your Eamil Verified NOW !!!'
                ]);
            } else {
                return response([
                    'message' => 'Something Went Wrong'
                ]);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }


    // Login Method
    public function login(LoginUserRequest $request)
    {
        try {
            // Validate the user inputs
            $request->validated();
            $user = User::where('email', $request->email)->first();
            if ($user->email_verified_at != null) {
                if ($request->email == $user->email and Hash::check($request->password, $user->password)) {
                    //give token after login and assign user id to token
                    $token = $this->createToken($user->id);
                    // check if user is already loggedin and assigned token 
                    if (Token::where('user_id', '=', $user->id)->first()) {
                        $token = Token::where('user_id', '=', $user->id)->first()->delete();
                        $new_token = $this->createToken($user->id);
                        // save token in db to user 
                        $token_save = Token::create([
                            'user_id' => $user->id,
                            'token' => $new_token
                        ]);
                        return response([
                            'Message' => "Already Login!",
                            "Token" => $new_token
                        ]);
                    } else {
                        // save token in db to user 
                        $token_save = Token::create([
                            'user_id' => $user->id,
                            'token' => $token
                        ]);

                        return response([
                            'Status' => '200',
                            'Message' => 'Successfully Login',
                            'Email' => $request->email,
                            'token' => $token
                        ], 200);
                    }
                } else {
                    return response([
                        'Status' => '400',
                        'message' => 'Bad Request',
                        'Error' => 'Email or Password doesnot match'
                    ], 400);
                }
            } else {
                return response([
                    'message' => 'An email has been sent with instructions to activate your account.Try checking your junk or spam filters.',
                    'Error' => 'Please Verify your Account First'
                ], 400);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function Logout(Request $request)
    {
        try {

            //call a helper function to decode user id
            $userID = DecodeUser($request);

            $userExist = Token::where("user_id", $userID)->first();

            if ($userExist) {
                $userExist->delete();
                return response([
                    "message" => "logout successfully"
                ], 200);
            } else {
                return response([
                    "message" => "This user is already logged out"
                ], 404);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    // Update user profile
    public function update(Request $request, $id)
    {
        try {
            //call a helper function to decode user id
            $userID = DecodeUser($request);
            $userupdate = User::all()->where('id', $id)->first();

            if ($userupdate->email_verified_at != null) {
                //message on Successfully
                if ($id == $userID) {
                    $input = $request->all();
                    if ($image = $request->file('profile_pic')) {
                        $destinationPath = 'profile/';
                        $profileImage = date('YmdHis') . "." . $image->getClientOriginalName();
                        $image->move($destinationPath, $profileImage);
                        $input['profile_pic'] = "$profileImage";
                    } else {
                        unset($input['profile_pic']);
                    }
                    //update other data
                    $userupdate->update($input);
                    //update password with Hash
                    if (isset($request->password)) {
                        User::where('id', $userID)->update(['password' => Hash::make($request->password)]);
                    }
                    return response([
                        'message' => 'you have successfully Update User Profile'
                    ], 200);
                } else {
                    return response([
                        'message' => 'UnAuthorize Access'
                    ], 200);
                }
                if ($userupdate == null) {
                    return response([
                        'Status' => '200',
                        'message' => 'You are not Authorized',
                    ], 404);
                }
            } else {
                return response([
                    'message' => 'An email has been sent with instructions to activate your account.Try checking your junk or spam filters.',
                    'Error' => 'Please Verify your Account First'
                ], 400);
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }
}
