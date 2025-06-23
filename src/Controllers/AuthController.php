<?php
namespace App\Controllers;

//use App\Models\UserModel;

class AuthController{

    public function login($request, $response, $args)
    {
        $data = $request->getParsedBody();
        // 这里请替换为你自己的用户校验
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if ($username === 'admin' && $password === '123456') {
            $token = \App\Helpers\JwtHelper::encode(['username' => $username]);
            $result = ['code' => 0, 'token' => $token, 'msg' => 'ok'];
        } else {
            $result = ['code' => 1, 'msg' => '用户名或密码错误'];
        }
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}