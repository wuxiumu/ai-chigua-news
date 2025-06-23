<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TitleHubController
{
    // 拉取美友饭的热门标题池，按日期写入md
    public function fetch(Request $request, Response $response, $args)
    {
        $date = $request->getQueryParams()['date'] ?? date('Y-m-d');
        $apiUrl = $date === date('Y-m-d')
            ? 'https://api.meiyoufan.com/tophub/'
            : 'https://api.meiyoufan.com/tophub/?date=' . $date;

        // 1. 拉取远程数据
        $json = @file_get_contents($apiUrl);
        if (!$json) {
            $response->getBody()->write(json_encode(['code'=>1, 'msg'=>'远程API请求失败']));
            return $response->withHeader('Content-Type', 'application/json');
        }
        $data = json_decode($json, true);
        if (!$data || empty($data['data'])) {
            $response->getBody()->write(json_encode(['code'=>1, 'msg'=>'API返回数据异常']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        // 2. 取标题（假设$data['data'] 是一维数组，或者嵌套['title'] 字段，根据实际结构调整）
        $titles = [];//JSON.data.items[0].title
        foreach($data['data']['items'] as $item) {
            // 假设每个$item有'title'字段
            if (!empty($item['title'])) $titles[] = trim($item['title']);
        }
        if (!$titles) {
            $response->getBody()->write(json_encode(['code'=>2, 'msg'=>'无标题数据']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        // 3. 写入 data/titlehub/{Y-m-d}.md
        $dir = __DIR__ . '/../../data/titlehub/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $filename = $dir . $date . '.md';
        // 直接覆盖写入
        file_put_contents($filename, implode("\n", $titles) . "\n");

        $response->getBody()->write(json_encode([
            'code'=>0,
            'msg'=>'ok',
            'count'=>count($titles),
            'file'=>basename($filename)
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
    private function baseDir() {
        return __DIR__ . '/../../data/titlehub/';
    }
    private function resolveFilename($name) {
        $base = preg_replace('/[^\w\-]/', '', basename($name, '.md'));
        return $base . '.md';
    }

    // 获取所有文件
    public function files(Request $request, Response $response) {
        $dir = $this->baseDir();
        $files = [];
        foreach (glob($dir . '*.md') as $f) {
            $files[] = basename($f, '.md');
        }
        $response->getBody()->write(json_encode(['code'=>0, 'data'=>$files]));
        return $response->withHeader('Content-Type', 'application/json');
    }
    // 新建文件
    public function createFile(Request $request, Response $response) {
        $name = trim($request->getParsedBody()['name'] ?? '');
        if (!$name) return $response->withStatus(400)->write(json_encode(['code'=>1,'msg'=>'文件名不能为空']));
        $file = $this->resolveFilename($name);
        $path = $this->baseDir() . $file;
        if (file_exists($path)) return $response->withStatus(409)->write(json_encode(['code'=>1,'msg'=>'文件已存在']));
        file_put_contents($path, '');
        $response->getBody()->write(json_encode(['code'=>0,'msg'=>'ok']));
        return $response->withHeader('Content-Type', 'application/json');
    }
    // 删除文件
    public function deleteFile(Request $request, Response $response, $args) {
        $file = $this->resolveFilename($args['filename']);
        $path = $this->baseDir() . $file;
        $metaPath = $path . '.meta.json';
        if (!file_exists($path)) return $response->withStatus(404)->write(json_encode(['code'=>1,'msg'=>'文件不存在']));
        unlink($path);
        if (file_exists($metaPath)) unlink($metaPath);
        $response->getBody()->write(json_encode(['code'=>0,'msg'=>'已删除']));
        return $response->withHeader('Content-Type', 'application/json');
    }
    // 重命名文件
    public function renameFile(Request $request, Response $response, $args) {
        $old = $this->resolveFilename($args['filename']);
        $new = trim($request->getParsedBody()['newname'] ?? '');
        if (!$new) return $response->withStatus(400)->write(json_encode(['code'=>1, 'msg'=>'新文件名不能为空']));
        $oldPath = $this->baseDir() . $old;
        $newFile = $this->resolveFilename($new);
        $newPath = $this->baseDir() . $newFile;
        $oldMeta = $oldPath . '.meta.json';
        $newMeta = $newPath . '.meta.json';
        if (!file_exists($oldPath)) return $response->withStatus(404)->write(json_encode(['code'=>1,'msg'=>'原文件不存在']));
        if (file_exists($newPath)) return $response->withStatus(409)->write(json_encode(['code'=>1,'msg'=>'新文件名已存在']));
        rename($oldPath, $newPath);
        if (file_exists($oldMeta)) rename($oldMeta, $newMeta);
        $response->getBody()->write(json_encode(['code'=>0,'msg'=>'已重命名']));
        return $response->withHeader('Content-Type', 'application/json');
    }
    // 获取某个文件内容及meta
    public function getFileContent(Request $request, Response $response, $args) {
        $file = $this->resolveFilename($args['filename']);
        $path = $this->baseDir() . $file;
        $metaPath = $path . '.meta.json';
        $lines = file_exists($path) ? file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        $meta = file_exists($metaPath) ? json_decode(file_get_contents($metaPath), true) : [];
        $data = [];
        foreach ($lines as $i=>$title) {
            $item = [
                'line_no'=>$i,
                'title'=>$title,
                'status'=>$meta[$title]['status'] ?? '未生成',
                'gen_at'=>$meta[$title]['gen_at'] ?? '',
                'err'=>$meta[$title]['err'] ?? '',
                'article_id'=>$meta[$title]['article'] ?? null
            ];
            $data[] = $item;
        }
        $response->getBody()->write(json_encode(['code'=>0,'data'=>$data]));
        return $response->withHeader('Content-Type', 'application/json');
    }
    // 新增标题行
    public function add(Request $request, Response $response, $args) {
        $params = $request->getParsedBody();
        $file = $this->resolveFilename($args['filename']);
        $title = trim($params['title'] ?? '');
        if (!$title) return $response->withStatus(400)->write(json_encode(['code'=>1, 'msg'=>'标题不能为空']));
        $path = $this->baseDir() . $file;
        file_put_contents($path, $title . "\n", FILE_APPEND);
        $response->getBody()->write(json_encode(['code'=>0, 'msg'=>'ok']));
        return $response->withHeader('Content-Type', 'application/json');
    }
    // 编辑标题行
    public function update(Request $request, Response $response, $args) {
        $line_no = intval($args['line_no']);
        $params = $request->getParsedBody();
        $file = $this->resolveFilename($args['filename']);
        $newTitle = trim($params['title'] ?? '');
        $path = $this->baseDir() . $file;
        $metaPath = $path . '.meta.json';
        $lines = file_exists($path) ? file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        if (!isset($lines[$line_no])) return $response->withStatus(404)->write(json_encode(['code'=>1,'msg'=>'未找到']));
        $oldTitle = $lines[$line_no];
        $lines[$line_no] = $newTitle;
        file_put_contents($path, implode("\n", $lines) . "\n");
        // 元数据迁移
        if (file_exists($metaPath)) {
            $meta = json_decode(file_get_contents($metaPath), true);
            if (isset($meta[$oldTitle])) {
                $meta[$newTitle] = $meta[$oldTitle];
                unset($meta[$oldTitle]);
                file_put_contents($metaPath, json_encode($meta, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
            }
        }
        $response->getBody()->write(json_encode(['code'=>0,'msg'=>'ok']));
        return $response->withHeader('Content-Type', 'application/json');
    }
    // 删除标题行
    public function del(Request $request, Response $response, $args) {
        $line_no = intval($args['line_no']);
        $file = $this->resolveFilename($args['filename']);
        $path = $this->baseDir() . $file;
        $metaPath = $path . '.meta.json';
        $lines = file_exists($path) ? file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        if (!isset($lines[$line_no])) return $response->withStatus(404)->write(json_encode(['code'=>1,'msg'=>'未找到']));
        $title = $lines[$line_no];
        unset($lines[$line_no]);
        file_put_contents($path, implode("\n", $lines) . "\n");
        if (file_exists($metaPath)) {
            $meta = json_decode(file_get_contents($metaPath), true);
            unset($meta[$title]);
            file_put_contents($metaPath, json_encode($meta, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        }
        $response->getBody()->write(json_encode(['code'=>0,'msg'=>'ok']));
        return $response->withHeader('Content-Type', 'application/json');
    }
    // AI生成+文章创建
    public function generate(Request $request, Response $response, $args) {
        $file = $this->resolveFilename($args['filename']);
        $line_no = intval($args['line_no']);
        $path = $this->baseDir() . $file;
        $metaPath = $path . '.meta.json';
        $lines = file_exists($path) ? file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        $title = $lines[$line_no] ?? '';
        if (!$title) return $response->withStatus(404)->write(json_encode(['code'=>1,'msg'=>'标题不存在']));
        $content = \App\Services\AliTongyiService::genMarkdownByTitle($title);
        $meta = file_exists($metaPath) ? json_decode(file_get_contents($metaPath), true) : [];
        $articleId = null;
        if ($content && class_exists('\App\Services\ArticleService')) {
            $articleId = \App\Services\ArticleService::createArticle([
                'title'   => $title,
                'content' => $content,
                'desc'    => mb_substr(\App\Helpers\Utils::markdownToText($content), 0, 100),
                'tags'    => [],
                'date'    => date('Y-m-d H:i:s')
            ]);
        }
        $meta[$title] = [
            'status'  => $content ? '已生成' : '失败',
            'gen_at'  => date('Y-m-d H:i:s'),
            'err'     => $content ? null : 'AI生成失败',
            'article' => $articleId
        ];
        file_put_contents($metaPath, json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $response->getBody()->write(json_encode([
            'code' => $content ? 0 : 1,
            'data' => [
                'content'    => $content,
                'status'     => $meta[$title]['status'],
                'article_id' => $articleId
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}