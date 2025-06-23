<?php
/**
 * qwen.php  —— 纯 cURL 调用通义千问
 * 需求：PHP 7.4 + curl 扩展
 */

function env(string $key, $default = null) {
    static $cache = null;
    if ($cache === null) {
        // 兼容 .env / config.ini / 真实环境变量
//        $envFile = __DIR__ . '/.env';
        $envFile ='/Users/mac/WebstormProjects/djibai/your-app-name/.env';
        if (file_exists($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (strpos($line, '=') !== false) {
                    [$k, $v] = array_map('trim', explode('=', $line, 2));
                    // 去掉左右的双引号
                    if (substr($v, 0, 1) === '"' && substr($v, -1) === '"') {
                        $v = substr($v, 1, -1);
                    }
                    $cache[$k] = $v;
                }
            }
        }
        // .ini 优先级更低
        $iniFile = __DIR__ . '/config.ini';
        if (file_exists($iniFile)) {
            $cache = array_merge($cache ?? [], parse_ini_file($iniFile));
        }
        // 补环境变量
        $cache = array_merge($cache ?? [], $_ENV, $_SERVER);
    }
//    var_dump($cache);die;
    return $cache[$key] ?? $default;
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