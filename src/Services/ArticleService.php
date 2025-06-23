<?php
namespace App\Services;

use App\Helpers\MarkdownHelper;
use Symfony\Component\Yaml\Yaml;


class ArticleService
{
    public static function getIndex() {
        $path = __DIR__ . '/../../data/index.json';
        if (!file_exists($path)) return [];
        $list = json_decode(file_get_contents($path), true);
        foreach ($list as $k => &$a) {
            if (empty($a['id'])) $a['id'] = $k;
//            $list[$k]['tags'] = implode(',', $a['tags']); // keywords 转字符串
        }
        unset($a);
        return $list;
    }

    public static function getArticle($id) {
        $path = __DIR__ . "/../../data/articles/{$id}.md";
        if (!file_exists($path)) return null;
        $content = file_get_contents($path);

        // 提取YAML头部
        if (preg_match('/^---(.*?)---(.*)$/s', $content, $m)) {
            $meta = Yaml::parse($m[1]);
            $mdContent = trim($m[2]);
        } else {
            $meta = [];
            $mdContent = $content;
        }
        $html = MarkdownHelper::render($mdContent);
        return ['meta' => $meta, 'content'=>$mdContent, 'content_html' => $html];
    }

    // 创建新文章
    public static function createArticle($data) {
        $id = uniqid(); // 你可以换成更好的ID生成逻辑
        $dir = __DIR__ . '/../../data/articles/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        // 准备 YAML 头部
        $meta = [
            'title' => $data['title'] ?? '',
            'desc' => $data['desc'] ?? '',
            'tags' => $data['tags'] ?? [],
            'cover' => $data['cover'] ?? '',
            'date' => date('Y-m-d H:i:s'),
        ];
        $yaml = "---\n" . Yaml::dump($meta) . "---\n";
        $content = $data['content'] ?? '';

        // 写入 .md 文件
        $file = $dir . $id . '.md';
        file_put_contents($file, $yaml . $content);

        // （可选）更新 index.json 索引
        self::updateIndex($id, $meta);

        return $id;
    }

    // 更新已有文章
    public static function updateArticle($id, $data) {
        $dir = __DIR__ . '/../../data/articles/';
        $file = $dir . $id . '.md';
        if (!file_exists($file)) return false;

        // 准备 YAML 头部
        $meta = [
            'title' => $data['title'] ?? '',
            'desc' => $data['desc'] ?? '',
            'tags' => $data['tags'] ?? [],
            'cover' => $data['cover'] ?? '',
            'date' => date('Y-m-d H:i:s'),
        ];
        $yaml = "---\n" . Yaml::dump($meta) . "---\n";
        $content = $data['content'] ?? '';

        // 覆盖 .md 文件
        file_put_contents($file, $yaml . $content);

        // 更新索引
        self::updateIndex($id, $meta);

        return true;
    }

    // 删除文章
    public static function deleteArticle($id) {
        $dir = __DIR__ . '/../../data/articles/';
        $file = $dir . $id . '.md';
        if (!file_exists($file)) return false;

        // 删除文章文件
        unlink($file);

        // 删除索引
        $indexPath = __DIR__ . '/../../data/index.json';
        if (file_exists($indexPath)) {
            $list = json_decode(file_get_contents($indexPath), true) ?: [];
            if (isset($list[$id])) {
                unset($list[$id]);
                file_put_contents($indexPath, json_encode($list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }
        }
        return true;
    }

    // 更新文章索引
    protected static function updateIndex($id, $meta) {
        $indexPath = __DIR__ . '/../../data/index.json';
        $list = file_exists($indexPath) ? json_decode(file_get_contents($indexPath), true) : [];
        $list[$id] = $meta;
        file_put_contents($indexPath, json_encode($list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

}