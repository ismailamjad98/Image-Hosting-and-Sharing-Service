<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

function DecodeUser(Request $request)
{
    $getToken = $request->bearerToken();
    
    if(!$getToken){
        return $userID = null;
    }
    dd($getToken);
    // $key = config('constant.key');
    $decoded = JWT::decode($getToken, new Key('SocialCamp', "HS256"));
    $userID = $decoded->id;
    return $userID;


}
