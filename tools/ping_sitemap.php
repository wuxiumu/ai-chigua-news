<?php
$sitemapUrl = urlencode('https://your-domain.com/sitemap.xml');

// 百度主动推送
$baiduPingUrl = "http://ping.baidu.com/ping/RPC2";
$baiduRes = file_get_contents("http://ping.baidu.com/ping/RPC2?sitemap=$sitemapUrl");

// Google
$googleRes = file_get_contents("https://www.google.com/ping?sitemap=$sitemapUrl");

// Bing
$bingRes = file_get_contents("https://www.bing.com/ping?sitemap=$sitemapUrl");

echo "Baidu: $baiduRes\nGoogle: $googleRes\nBing: $bingRes\n";
// 0 3 * * * php /path/to/your/tools/ping_sitemap.php >> /path/to/your/log/ping_sitemap.log 2>&1