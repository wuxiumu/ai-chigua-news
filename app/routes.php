<?php

declare(strict_types=1);

use App\Controllers\ArticleController;
use Slim\App;
use App\Controllers\SeoController;
use App\Application\Middleware\JwtMiddleware;
use App\Controllers\AuthController;

use Tuupola\Middleware\CorsMiddleware;
use App\Controllers\TitlePoolController;
use App\Controllers\TitleHubController;
use App\Controllers\ConfigController;

return function (App $app) {

    $app->add(new CorsMiddleware([
        "origin" => ["*"], // 生产环境请只写你的前端域名
        "methods" => ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
        "headers.allow" => ["Authorization", "Content-Type"],
        "headers.expose" => [],
        "credentials" => false,
        "cache" => 0,
    ]));

    $app->post('/api/login', [AuthController::class, 'login']);
    $app->get('/api/articlelist',  [ArticleController::class, 'apiList']);
    $app->get('/api/articleinfo/{id}',  [ArticleController::class, 'apiShow']);
    $app->get('/api/site-config', [ConfigController::class, 'getSiteConfig']);
    $app->get('/api/site-theme', [ConfigController::class, 'getSiteTheme']);
    $app->get('/api/theme/articlelist',  [ConfigController::class, 'getCurrentTheme']);//获取当前主题的文章列表
    $app->get('/api/config', [ArticleController::class, 'apiConfig']); // tdk配置
    $app->group('/api', function ($group) {
        //  修改密码
        $group->post('/password', [AuthController::class, 'setPassword']);
        $group->post('/site-config', [ConfigController::class, 'setSiteConfig']);
        $group->post('/site-theme', [ConfigController::class, 'setSiteTheme']);


        //  注册
        // 文章管理
        $group->get('/articles', [ArticleController::class, 'apiList']);
        $group->get('/article/{id}', [ArticleController::class, 'apiShow']);
        $group->post('/article', [ArticleController::class, 'apiCreate']);
        $group->put('/article/{id}', [ArticleController::class, 'apiUpdate']);
        $group->delete('/article/{id}', [ArticleController::class, 'apiDelete']);


        // 文件管理
        $group->get('/title-files', [TitlePoolController::class, 'files']);               // 获取所有文件
        $group->post('/title-file', [TitlePoolController::class, 'createFile']);          // 新建文件
        $group->delete('/title-file/{filename}', [TitlePoolController::class, 'deleteFile']); // 删除文件
        $group->put('/title-file/{filename}', [TitlePoolController::class, 'renameFile']);    // 重命名文件

        // 文件内容（标题池）管理
        $group->get('/title-file/{filename}', [TitlePoolController::class, 'getFileContent']);   // 获取内容+meta
        $group->post('/title-file/{filename}/line', [TitlePoolController::class, 'add']);        // 新增一行标题
        $group->put('/title-file/{filename}/line/{line_no}', [TitlePoolController::class, 'update']); // 编辑一行
        $group->delete('/title-file/{filename}/line/{line_no}', [TitlePoolController::class, 'del']); // 删除一行
        $group->post('/title-file/{filename}/line/{line_no}/generate', [TitlePoolController::class, 'generate']); // AI生成

        $group->get('/titlehub/fetch', [TitleHubController::class, 'fetch']);

    })->add(JwtMiddleware::class);

    $app->group('/api/titlehub', function ($group) {
        $group->get('/files', [TitleHubController::class, 'files']);               // 获取所有md文件
        $group->post('/file', [TitleHubController::class, 'createFile']);          // 新建md文件
        $group->delete('/file/{filename}', [TitleHubController::class, 'deleteFile']); // 删除md文件
        $group->put('/file/{filename}', [TitleHubController::class, 'renameFile']);    // 重命名md文件

        $group->get('/file/{filename}', [TitleHubController::class, 'getFileContent']); // 获取文件所有行+meta
        $group->post('/file/{filename}/line', [TitleHubController::class, 'add']);      // 新增一行
        $group->put('/file/{filename}/line/{line_no}', [TitleHubController::class, 'update']); // 修改一行
        $group->delete('/file/{filename}/line/{line_no}', [TitleHubController::class, 'del']); // 删除一行
        $group->post('/file/{filename}/line/{line_no}/generate', [TitleHubController::class, 'generate']); // AI生成并建文
    })->add(JwtMiddleware::class);

    $app->get('/in', [ArticleController::class, 'index']);
    $app->get('/', [ArticleController::class, 'home']);


    $app->get('/article/{id}', [ArticleController::class, 'show']);


    $app->get('/tags/{tag}', [ArticleController::class, 'tag']);
    $app->get('/hot', [ArticleController::class, 'hot']);

    $app->post('/submit', [ArticleController::class, 'submit']); // 可选

    $app->get('/sitemap.xml', [SeoController::class, 'sitemap']);
    $app->get('/robots.txt', function($req, $res, $args){
        $txt = "User-agent: *\nAllow: /\nSitemap: " . ($_ENV['APP_URL'] ?? '') . "/sitemap.xml";
        $res->getBody()->write($txt);
        return $res->withHeader('Content-Type', 'text/plain');
    });


};
