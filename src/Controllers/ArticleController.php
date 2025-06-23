<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\ArticleService;

class ArticleController
{
    public function index(Request $request, Response $response, $args): Response
    {
        $articles = ArticleService::getIndex();
        $siteName = $_ENV['APP_NAME'];
        $siteDesc = $_ENV['APP_DESC'];
        $siteKeywords = $_ENV['APP_KEYWORDS'];
        $domain = $_ENV['APP_URL'];

        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{$siteName} - 明星爆料列表</title>
    <meta name="description" content="{$siteDesc}">
    <meta name="keywords" content="{$siteKeywords}">
    <link rel="canonical" href="{$domain}/">
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{$siteName} - 明星爆料列表" />
    <meta property="og:description" content="{$siteDesc}" />
    <meta property="og:url" content="{$domain}/" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h1>{$siteName}</h1>
    <ul>
HTML;
        foreach ($articles as $key => $a) {
            $a['id'] = $key;
            $tags = '';
            if (!empty($a['tags'])) {
                foreach ($a['tags'] as $tag) {
                    $tags .= "<a href='/tags/".urlencode($tag)."'>#{$tag}</a> ";
                }
            }
            $html .= "<li><a href='/article/{$a['id']}'>{$a['title']}</a> <small>{$a['date']}</small> {$tags}</li>";
        }
        $html .= <<<HTML
    </ul>
</body>
</html>
HTML;
        $response->getBody()->write($html);
        return $response;
    }

    public function show(Request $request, Response $response, $args): Response
    {
        $id = $args['id'];
        $data = ArticleService::getArticle($id);
        if (!$data) {
            $response->getBody()->write("瓜不存在或已被吃掉");
            return $response->withStatus(404);
        }
        $meta = $data['meta'];
        $domain = $_ENV['APP_URL'];
        $siteName = $_ENV['APP_NAME'];
        $meta['desc'] = $meta['desc']?? '';
        $meta['cover'] = $meta['cover']?? '';
        $meta['tags'] = $meta['tags']?? [];
        $meta['date'] = $meta['date']??date('Y-m-d');
        // 结构化数据
        $jsonld = [
            "@context" => "https://schema.org",
            "@type" => "NewsArticle",
            "headline" => $meta['title'],
            "datePublished" => date('c', strtotime($meta['date'])),
            "description" => $meta['desc'],
            "mainEntityOfPage" => [
                "@type" => "WebPage",
                "@id" => $domain . '/article/' . $id
            ],
            "image" => !empty($meta['cover']) ? $domain . $meta['cover'] : null,
            "author" => [
                "@type" => "Organization",
                "name" => $siteName
            ]
        ];
        $tagsHtml = '';
        if (!empty($meta['tags'])) {
            foreach ($meta['tags'] as $tag) {
                $tagsHtml .= "<a href='/tags/".urlencode($tag)."'>#{$tag}</a> ";
            }
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{$meta['title']} - {$siteName}</title>
    <meta name="description" content="{$meta['desc']}">
    <meta name="keywords" content="{$tagsHtml}">
    <link rel="canonical" href="{$domain}/article/{$id}">
    <meta property="og:type" content="article" />
    <meta property="og:title" content="{$meta['title']}" />
    <meta property="og:description" content="{$meta['desc']}" />
    <meta property="og:url" content="{$domain}/article/{$id}" />
    <meta property="og:image" content="{$domain}{$meta['cover']}" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="application/ld+json">
    {$this->jsonld($jsonld)}
    </script>
</head>
<body>
    <h1>{$meta['title']}</h1>
    <p>{$meta['desc']}</p>
    <div>{$data['content_html']}</div>
    <p>{$tagsHtml}</p>
    <a href="/">返回首页</a>
</body>
</html>
HTML;
        $response->getBody()->write($html);
        return $response;
    }

    private function jsonld($arr) {
        return json_encode($arr, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    }

    public function tag(Request $request, Response $response, $args): Response
    {
        $tag = urldecode($args['tag']);
        $articles = ArticleService::getIndex();
        $domain = $_ENV['APP_URL'];
        $siteName = $_ENV['APP_NAME'];

        $filtered = [];
        foreach ($articles as $a) {
            if (!empty($a['tags']) && in_array($tag, $a['tags'])) $filtered[] = $a;
        }
        $desc = "{$siteName}标签：{$tag} 相关爆料合集";
        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{$tag} - {$siteName}标签页</title>
    <meta name="description" content="{$desc}">
    <meta name="keywords" content="{$tag}">
    <link rel="canonical" href="{$domain}/tags/{$tag}">
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{$tag} - {$siteName}标签页" />
    <meta property="og:description" content="{$desc}" />
    <meta property="og:url" content="{$domain}/tags/{$tag}" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h1>#{$tag} 爆料</h1>
    <ul>
HTML;
        foreach ($filtered as $k => $a) {
            $id = $a['id'] ?? $k;
            $html .= "<li><a href='/article/{$id}'>{$a['title']}</a> <small>{$a['date']}</small></li>";
        }
        $html .= <<<HTML
    </ul>
    <a href="/">返回首页</a>
</body>
</html>
HTML;
        $response->getBody()->write($html);
        return $response;
    }

    // 获取所有文章（列表接口，给前端后台用）
    public function apiList(Request $request, Response $response, $args): Response
    {
        $params = $request->getQueryParams();
        $page = max(1, intval($params['page'] ?? 1));
        $size = max(1, min(50, intval($params['size'] ?? 10))); // 最大每页50
        $search = trim($params['search'] ?? '');
        $tag = trim($params['tag'] ?? '');

        $all = ArticleService::getIndex();

        // 搜索/筛选
        $filtered = array_filter($all, function($a) use ($search, $tag) {
            $match = true;
            if ($search) {
                $content = ($a['title'] ?? '') . ($a['desc'] ?? '') . ($a['content'] ?? '');
                $match = mb_stripos($content, $search) !== false;
            }
            if ($tag) {
                $tags = $a['tags'] ?? [];
                if (!is_array($tags)) $tags = [];
                $match = $match && in_array($tag, $tags);
            }
            return $match;
        });

        // 排序（按 date desc）
        usort($filtered, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        $total = count($filtered);
        $result = array_slice(array_values($filtered), ($page-1)*$size, $size);

        $resp = [
            'list' => $result,
            'total' => $total,
            'page' => $page,
            'size' => $size
        ];
        $response->getBody()->write(json_encode(['code'=>0, 'data'=>$resp], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

// 获取单篇文章详情
    public function apiShow(Request $request, Response $response, $args): Response
    {
        $id = $args['id'] ?? '';
        $article = ArticleService::getArticle($id);
        if (!$article) {
            $response->getBody()->write(json_encode(['code'=>1,'msg'=>'not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        $response->getBody()->write(json_encode(['code'=>0, 'data'=>$article], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

// 新建文章
    public function apiCreate(Request $request, Response $response, $args): Response
    {
        $data = $request->getParsedBody();

        // 1. 必要字段校验
        if (empty($data['title'])) {
            $response->getBody()->write(json_encode(['code'=>1, 'msg'=>'标题不能为空']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        try {
            $id = ArticleService::createArticle($data);
            if (!$id) {
                throw new \Exception("文章保存失败");
            }
            // 可选：获取新建文章内容
            $article = ArticleService::getArticle($id);
            $response->getBody()->write(json_encode([
                'code' => 0,
                'msg' => 'ok',
                'id' => $id,
                'data' => $article
            ], JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'code' => 1,
                'msg' => '新建失败：' . $e->getMessage()
            ]));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

// 更新文章
    public function apiUpdate(Request $request, Response $response, $args): Response
    {
        $id = $args['id'] ?? '';
        $data = $request->getParsedBody();
        $ok = ArticleService::updateArticle($id, $data);
        $response->getBody()->write(json_encode(['code'=>$ok?0:1, 'msg'=>$ok?'ok':'fail']));
        return $response->withHeader('Content-Type', 'application/json');
    }

// 删除文章
    public function apiDelete(Request $request, Response $response, $args): Response
    {
        $id = $args['id'] ?? '';
        $ok = ArticleService::deleteArticle($id);
        $response->getBody()->write(json_encode(['code'=>$ok?0:1, 'msg'=>$ok?'ok':'fail']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function apiConfig(Request $request, Response $response, $args): Response
    {
        $keywords = $_ENV['APP_KEYWORDS'] ?? '';
//        $keywords = explode(',', $keywords); // , 切割关键字
        $data = [
            'title' => $_ENV['APP_NAME'] ?? '',
            'desc' => $_ENV['APP_DESC'] ?? '',
            'keywords' =>$keywords,
            'url' => $_ENV['APP_URL'] ?? ''
        ];
        $response->getBody()->write(json_encode(['code'=>0, 'data'=>$data], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 函数home。输出/public/home.html文件内容
    public function home(Request $request, Response $response, $args): Response
    {
        $html = file_get_contents(dirname(__DIR__).'../../public/home.html');
        $response->getBody()->write($html);
        return $response;
    }
}