<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

class createToken{
    protected $key;
    protected $payload;
    public function createToken($data)
    {
        try {
            $key = config('constant.key');
            $payload = array(
                "iss" => "http://127.0.0.1:8000",
                "aud" => "http://127.0.0.1:8000/api",
                "iat" => time(),
                "nbf" => 1357000000,
                "id" => $data,
                'token_type' => 'bearer',
            );

            $token = JWT::encode($payload, $key, 'HS256');
            return $token;
        } catch (Throwable $e) {
            return response(['message' => $e->getMessage()]);
        }
    }
}