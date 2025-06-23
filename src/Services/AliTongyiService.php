<?php

namespace App\Services;

class AliTongyiService
{
    // 用于云通义大模型（文心千帆API）生成 markdown 文章
    public static function genMarkdownByTitle($title)
    {
        // 推荐从环境变量或配置获取密钥
        $apiKey = $_ENV['DASHSCOPE_API_KEY']; // 你的云通义API密钥
        if (!$apiKey) {
            error_log("通义 API Key 未配置");
            return null;
        }

        $url = $_ENV['DASHSCOPE_BASE_URL'];

        $temperature = 0.8;
        // 1) 组装请求体
        $payload = json_encode([
            'model'    => "qwen-turbo",
            'messages' => [
                [
                    'role' => 'user',
                    'content' =>
                        "请用Markdown格式为以下新闻标题写一篇不少于50字、带小标题、分段的爆料/资讯文章：\n标题：{$title}"
                ]
            ],
            'temperature' => $temperature,
        ], JSON_UNESCAPED_UNICODE);

        // 2) 初始化 cURL
        $ch = curl_init("$url/chat/completions");
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
        $res = json_decode($raw, true);
        if (!isset($res['choices'][0]['message']['content'])) {
            throw new \Exception("Unexpected response: $raw");
        }
        return $res['choices'][0]['message']['content'];
    }
}