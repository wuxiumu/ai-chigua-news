<?php
namespace App\Services;

use App\Helpers\MarkdownHelper;
use Symfony\Component\Yaml\Yaml;
class TitlePoolService {
    // 读取标题池文件所有行，返回带结构的数组
    // 获取标题和meta
    public function getTitles(Request $request, Response $response, $args) {
        $file = basename($request->getQueryParams()['file'] ?? 'titles.md');
        $path = __DIR__ . "/../../data/title_files/$file";
        $metaPath = $path . '.meta.json';
        $lines = file_exists($path) ? file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        $meta = file_exists($metaPath) ? json_decode(file_get_contents($metaPath), true) : [];
        $data = [];
        foreach ($lines as $i => $title) {
            $item = [
                'line_no' => $i,
                'title' => $title,
                'status' => $meta[$title]['status'] ?? '未生成',
                'gen_at' => $meta[$title]['gen_at'] ?? null
            ];
            $data[] = $item;
        }
        $response->getBody()->write(json_encode(['code'=>0, 'data'=>$data], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 新增一行标题
    public static function addTitle($file, $title) {
        $line = "$title|pending||";
        file_put_contents(__DIR__."/../../data/titlepool/$file", $line."\n", FILE_APPEND);
    }
    // 编辑/删除/状态更新等（按序号或内容匹配即可）
    public static function updateTitle($file, $idx, $status, $time = '', $article_id = '') {
        $titles = self::getTitles($file);
        if (!isset($titles[$idx])) return false;
        $titles[$idx]['status'] = $status;
        $titles[$idx]['time'] = $time;
        $titles[$idx]['article_id'] = $article_id;
        // 保存回文件
        $lines = [];
        foreach ($titles as $t) {
            $lines[] = "{$t['title']}|{$t['status']}|{$t['time']}|{$t['article_id']}";
        }
        file_put_contents(__DIR__."/../../data/titlepool/$file", implode("\n", $lines));
        return true;
    }
}