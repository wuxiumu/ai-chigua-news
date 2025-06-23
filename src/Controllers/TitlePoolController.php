<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\AliTongyiService;
use App\Helpers\Utils;

class TitlePoolController
{
    // 补全 .md 后缀，仅允许安全字符
    private function resolveFilename($name) {
        $base = preg_replace('/[^\w\-]/', '', basename($name, '.md'));
        return $base . '.md';
    }

    // 文件列表
    public function files(Request $request, Response $response) {
        $dir = __DIR__.'/../../data/titlepool/';
        $files = [];
        foreach (glob($dir.'*.md') as $f) {
            $files[] = basename($f, '.md'); // 只返回主名
        }
        $response->getBody()->write(json_encode(['code'=>0, 'data'=>$files]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 新建文件
    public function createFile(Request $request, Response $response) {
        $name = trim($request->getParsedBody()['name'] ?? '');
        if (!$name) return $response->withStatus(400)->write(json_encode(['code'=>1,'msg'=>'文件名不能为空']));
        $file = $this->resolveFilename($name);
        $path = __DIR__."/../../data/titlepool/{$file}";
        if (file_exists($path)) return $response->withStatus(409)->write(json_encode(['code'=>1,'msg'=>'文件已存在']));
        file_put_contents($path, '');
        $response->getBody()->write(json_encode(['code'=>0,'msg'=>'ok']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 删除文件
    public function deleteFile(Request $request, Response $response, $args) {
        $file = $this->resolveFilename($args['filename']);
        $dir = __DIR__.'/../../data/titlepool/';
        $path = $dir . $file;
        $metaPath = $path . '.meta.json';
        if (!file_exists($path)) {
            return $response->withStatus(404)->write(json_encode(['code'=>1, 'msg'=>'文件不存在']));
        }
        unlink($path);
        if (file_exists($metaPath)) unlink($metaPath);
        $response->getBody()->write(json_encode(['code'=>0, 'msg'=>'已删除']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 重命名文件
    public function renameFile(Request $request, Response $response, $args) {
        $old = $this->resolveFilename($args['filename']);
        $new = trim($request->getParsedBody()['newname'] ?? '');
        if (!$new) {
            return $response->withStatus(400)->write(json_encode(['code'=>1, 'msg'=>'新文件名不能为空']));
        }
        $dir = __DIR__.'/../../data/titlepool/';
        $oldPath = $dir . $old;
        $newFile = $this->resolveFilename($new);
        $newPath = $dir . $newFile;
        $oldMeta = $oldPath . '.meta.json';
        $newMeta = $newPath . '.meta.json';
        if (!file_exists($oldPath)) {
            return $response->withStatus(404)->write(json_encode(['code'=>1, 'msg'=>'原文件不存在']));
        }
        if (file_exists($newPath)) {
            return $response->withStatus(409)->write(json_encode(['code'=>1, 'msg'=>'新文件名已存在']));
        }
        rename($oldPath, $newPath);
        if (file_exists($oldMeta)) {
            rename($oldMeta, $newMeta);
        }
        $response->getBody()->write(json_encode(['code'=>0, 'msg'=>'已重命名']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 获取某个文件全部行及meta
    public function getFileContent(Request $request, Response $response, $args) {
        $file = $this->resolveFilename($args['filename']);
        $dir = __DIR__.'/../../data/titlepool/';
        $path = $dir . $file;
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
                'err'=>$meta[$title]['err'] ?? ''
            ];
            $data[] = $item;
        }
        $response->getBody()->write(json_encode(['code'=>0,'data'=>$data]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 新增标题
    public function add(Request $request, Response $response, $args) {
        $params = $request->getParsedBody();
        $file = $this->resolveFilename($args['filename'] ?? $params['file'] ?? 'titles');
        $title = trim($params['title'] ?? '');
        if (!$title) return $response->withStatus(400)->write(json_encode(['code'=>1, 'msg'=>'标题不能为空']));
        $dir = __DIR__ . '/../../data/titlepool/';
        $path = $dir . $file;
        file_put_contents($path, $title . "\n", FILE_APPEND);
        $response->getBody()->write(json_encode(['code'=>0, 'msg'=>'ok']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 修改某行标题
    public function update(Request $request, Response $response, $args) {
        $line_no = intval($args['line_no']);
        $params = $request->getParsedBody();
        $file = $this->resolveFilename($args['filename'] ?? $params['file'] ?? 'titles');
        $newTitle = trim($params['title'] ?? '');
        $dir = __DIR__ . '/../../data/titlepool/';
        $path = $dir . $file;
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

    // 删除某行标题
    public function del(Request $request, Response $response, $args) {
        $line_no = intval($args['line_no']);
        $file = $this->resolveFilename($args['filename'] ?? $request->getQueryParams()['file'] ?? 'titles');
        $dir = __DIR__ . '/../../data/titlepool/';
        $path = $dir . $file;
        $metaPath = $path . '.meta.json';
        $lines = file_exists($path) ? file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        if (!isset($lines[$line_no])) return $response->withStatus(404)->write(json_encode(['code'=>1,'msg'=>'未找到']));
        $title = $lines[$line_no];
        unset($lines[$line_no]);
        file_put_contents($path, implode("\n", $lines) . "\n");
        // 删除元数据
        if (file_exists($metaPath)) {
            $meta = json_decode(file_get_contents($metaPath), true);
            unset($meta[$title]);
            file_put_contents($metaPath, json_encode($meta, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        }
        $response->getBody()->write(json_encode(['code'=>0,'msg'=>'ok']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // AI生成内容，标记状态/时间，创建文章
    public function generate(Request $request, Response $response, $args) {
        $file = $this->resolveFilename($args['filename'] ?? $request->getQueryParams()['file'] ?? 'titles');
        $line_no = intval($args['line_no']);
        $dir = __DIR__ . '/../../data/titlepool/';
        $path = $dir . $file;
        $metaPath = $path . '.meta.json';
        $lines = file_exists($path) ? file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        $title = $lines[$line_no] ?? '';
        if (!$title) {
            return $response->withStatus(404)->write(json_encode(['code'=>1,'msg'=>'标题不存在']));
        }

        // 1. AI生成内容
        $content = \App\Services\AliTongyiService::genMarkdownByTitle($title);
        $meta = file_exists($metaPath) ? json_decode(file_get_contents($metaPath), true) : [];

        $articleId = null;
        if ($content) {
            // 2. 新建文章入库
            if (class_exists('\App\Services\ArticleService')) {
                $articleId = \App\Services\ArticleService::createArticle([
                    'title'   => $title,
                    'content' => $content,
                    'desc'    => mb_substr(Utils::markdownToText($content), 0, 100), // 摘要，可自定义
                    'tags'    => [], // 你可以解析自动标签，也可前端手动填
                    'date'    => date('Y-m-d H:i:s')
                ]);
            }
        }

        // 3. 标记meta
        $meta[$title] = [
            'status'  => $content ? '已生成' : '失败',
            'gen_at'  => date('Y-m-d H:i:s'),
            'err'     => $content ? null : 'AI生成失败',
            'article' => $articleId // 新增文章ID字段，前端可以跳转
        ];
        file_put_contents($metaPath, json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        // 4. 返回内容、状态、文章ID
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

    /**
     * 向 Qwen 发送 Chat 请求
     * @param array  $messages   OpenAI 格式的消息数组
     * @param string $model      模型名
     * @param float  $temperature
     * @return array             [answer, usage] 或抛异常
     * @throws Exception
     */
    function qwenChat(array $messages, string $model = null, float $temperature = 0.8): array
    {
        $apiKey   = env('DASHSCOPE_API_KEY');
        $baseUrl  = rtrim(env('DASHSCOPE_BASE_URL'), '/');
        $model    = $model ?? env('QWEN_MODEL', 'qwen-turbo');

        if (!$apiKey || !$baseUrl) {
            throw new Exception('缺少 DASHSCOPE_API_KEY 或 DASHSCOPE_BASE_URL 配置');
        }
        // 1) 组装请求体
        $payload = json_encode([
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $temperature,
        ], JSON_UNESCAPED_UNICODE);

        // 2) 初始化 cURL
        $ch = curl_init("$baseUrl/chat/completions");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer $apiKey",
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => $payload,
            // 如需代理／超时可自行加：
            // CURLOPT_PROXY => 'http://127.0.0.1:7890',
            CURLOPT_TIMEOUT        => 30,
        ]);

        // 3) 执行并解析
        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new Exception("cURL Error: $err");
        }
        if ($httpCode !== 200) {
            throw new Exception("HTTP $httpCode → $raw");
        }

        $res = json_decode($raw, true);
        if (!isset($res['choices'][0]['message']['content'])) {
            throw new Exception("Unexpected response: $raw");
        }

        return [
            'answer' => $res['choices'][0]['message']['content'],
            'usage'  => $res['usage'] ?? null,
        ];
    }
}