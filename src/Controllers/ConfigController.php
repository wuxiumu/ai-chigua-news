<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConfigController
{
    // 配置文件路径
    private $configPath = __DIR__ . '/../../data/site-config.json';
    private $configThemePath = __DIR__ . '/../../data/site-theme.json';
    private $configCurrentThemePath = __DIR__ . '/../../data/current-theme.json';

    // 获取完整配置
    public function getSiteConfig(Request $request, Response $response)
    {
        if (!file_exists($this->configPath)) {
            // 初始化一个默认配置
            $default = [
                "navbar" => [],
                "banner" => [],
                "carousel" => ["slides" => []],
                "popularTags" => [],
                "footer" => [],
            ];
            file_put_contents($this->configPath, json_encode($default, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        }
        $data = json_decode(file_get_contents($this->configPath), true);
        $response->getBody()->write(json_encode([
            "code" => 0,
            "data" => $data,
        ], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 保存完整配置
    public function setSiteConfig(Request $request, Response $response)
    {
        $params = $request->getParsedBody();
        if (empty($params)) {
            $params = json_decode($request->getBody()->getContents(), true);
        }
        if (!is_array($params)) {
            $response->getBody()->write(json_encode(["code"=>1, "msg"=>"参数错误"]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        file_put_contents($this->configPath, json_encode($params, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        file_put_contents($this->configCurrentThemePath, json_encode($params, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));// bug: 保存当前主题配置
        $response->getBody()->write(json_encode(["code"=>0, "msg"=>"ok"]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 获取轮播配置（如果只需要单独接口，可以加这个）
    public function getCarousel(Request $request, Response $response)
    {
        $data = json_decode(file_get_contents($this->configPath), true);
        $carousel = $data['carousel'] ?? ["slides" => []];
        $response->getBody()->write(json_encode([
            "code" => 0,
            "data" => $carousel
        ], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 保存轮播配置（可选单独接口）
    public function setCarousel(Request $request, Response $response)
    {
        $params = $request->getParsedBody();
        if (empty($params)) {
            $params = json_decode($request->getBody()->getContents(), true);
        }
        if (!isset($params['slides']) || !is_array($params['slides'])) {
            $response->getBody()->write(json_encode(["code"=>1, "msg"=>"slides格式错误"]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        $data = json_decode(file_get_contents($this->configPath), true);
        $data['carousel'] = $params;
        file_put_contents($this->configPath, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        $response->getBody()->write(json_encode(["code"=>0, "msg"=>"ok"]));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function getSiteTheme(Request $request, Response $response)
    {
        if (!file_exists($this->configThemePath)) {
            // 初始化一个默认配置
            $default = '[{"order":1,"name":"蓝色","description":"博客主题","author":"Aric","version":"1","preview":"https:\/\/archive.biliimg.com\/bfs\/archive\/65fdf3ecfbf72ee5f393ad64d270135806b60e14.png","is_used":1,"theme_path":"\/theme-lib\/classic\/home.html"},{"order":2,"name":"粉色","description":"博客","author":"Aric","theme_path":"\/theme-lib\/pink\/home.html","version":"1","preview":"https:\/\/archive.biliimg.com\/bfs\/archive\/4649037dc59ae6dea8c81cdd089ab95514902d06.png","is_used":0}]';
            file_put_contents($this->configThemePath, $default);
        }
        $data = json_decode(file_get_contents($this->configThemePath), true);
        $response->getBody()->write(json_encode([
            "code" => 0,
            "data" => $data,
        ], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function setSiteTheme(Request $request, Response $response)
    {
        $params = $request->getParsedBody();
        if (empty($params)) {
            $params = json_decode($request->getBody()->getContents(), true);
        }
        if (!is_array($params)) {
            $response->getBody()->write(json_encode(["code"=>1, "msg"=>"参数错误"]));
            return $response->withHeader('Content-Type', 'application/json');
        }
        // 保证只有一个is_used=1，如果前端传多了/全为0，自动修正第一个为1
        $hasUsed = false;
        foreach ($params as $k => &$theme) {
            $theme['is_used'] = intval($theme['is_used']);
            if ($theme['is_used'] == 1) {
                // 获取当前项目路径
                $current_theme_path = __DIR__ . '/../../public';
                // 获取内容 存入index.html
                //判断文件夹是否存在
                if (file_exists($current_theme_path . $theme['theme_path'])) {
                    $res = file_get_contents($current_theme_path . $theme['theme_path']); // 获取主题内容
                    file_put_contents($current_theme_path . '/index.html', $res); // 写入index.html
                }
                if (!$hasUsed) {
                    $hasUsed = true;
                } else {
                    $theme['is_used'] = 0; // 只保留第一个为1
                }
            }
        }
        // 启用有效的
        unset($theme); // 释放引用

        // 如果没有is_used=1，则第一个设为1
        if (!$hasUsed && count($params) > 0) {
            $params[0]['is_used'] = 1;
        }

        file_put_contents($this->configThemePath, json_encode($params, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        $response->getBody()->write(json_encode(["code"=>0, "msg"=>"ok"]));
        return $response->withHeader('Content-Type', 'application/json');
    }
    // currenTheme
    public function getCurrentTheme(Request $request, Response $response)
    {
        $data = json_decode(file_get_contents($this->configCurrentThemePath), true);
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');

    }
}
