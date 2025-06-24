<?php
namespace App\Controllers;


class AuthController{


    public function login($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $user = getUserData();
        if (!$user) {
            // 没有用户数据文件，只允许 admin/123456
            if ($username === 'admin' && $password === '123456') {
                // 登录成功，但必须设置新密码，返回特殊 code
                $token = \App\Helpers\JwtHelper::encode(['username' => $username]);
                $result = ['code' => 2, 'token' => $token, 'msg' => '首次登录，请设置新账号密码'];
                // 生成新的用户文件，给下次登录使用
                setUserData('admin', md5('123456'));
            } else {
                $result = ['code' => 1, 'msg' => '用户名或密码错误'];
            }
        } else {
            // 有用户数据，必须用新账号密码
            if ($username === $user['username'] && md5($password) === $user['password']) {
                $token = \App\Helpers\JwtHelper::encode(['username' => $username]);
                $result = ['code' => 0, 'token' => $token, 'msg' => 'ok'];
            } else {
                $result = ['code' => 1, 'msg' => '用户名或密码错误'];
            }
        }
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function setPassword($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        if (!$username || !$password) {
            $result = ['code' => 1, 'msg' => '用户名和密码不能为空'];
        } else if ($username === 'admin' || $password === '123456') {
            $result = ['code' => 2, 'msg' => '不能再使用默认账号或密码'];
        } else {
            setUserData($username, $password);
            $result = ['code' => 0, 'msg' => '账号密码已设置成功'];
        }
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

}

function getUserFile() {
    return __DIR__ . '/../../data/users.json';
}
function getUserData() {
    $file = getUserFile();
    if (!file_exists($file)) {
        return null;
    }
    $json = file_get_contents($file);
    return json_decode($json, true);
}
function setUserData($username, $password) {
    $file = getUserFile();
    $data = [
        'username' => $username,
        'password' => md5($password) // 最简单的hash，可替换更安全的hash算法
    ];
    file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}