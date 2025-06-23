<?php
namespace App\Application\Middleware;


use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;
use App\Helpers\JwtHelper;

class JwtMiddleware
{
    public function __invoke(Request $request, Handler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->unauth();
        }
        $jwt = $matches[1];
        try {
            $user = JwtHelper::decode($jwt);
            // 可将用户信息注入请求属性
            $request = $request->withAttribute('user', $user);// 注入用户信息
            return $handler->handle($request);
        } catch (\Exception $e) {

            return $this->unauth($e->getMessage());
        }
    }

    private function unauth($msg = '未登录或token无效')
    {
        $res = new SlimResponse();
        $res->getBody()->write(json_encode(['code' => 401, 'msg' => $msg]));
        return $res->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
}