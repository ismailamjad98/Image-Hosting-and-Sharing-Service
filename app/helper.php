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
    $key = config('constant.key');
    $decoded = JWT::decode($getToken, new Key($key, "HS256"));
    $userID = $decoded->id;
    return $userID;


}
