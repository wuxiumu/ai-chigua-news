<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper
{
    public static function encode($payload)
    {
        $key = $_ENV['JWT_SECRET'];
        $expire = $_ENV['JWT_EXPIRE'] ?? 86400;
        $payload['exp'] = time() + $expire;
        return JWT::encode($payload, $key, 'HS256');
    }

    public static function decode($token)
    {
        $key = $_ENV['JWT_SECRET'];
        return JWT::decode($token, new Key($key, 'HS256'));
    }
}