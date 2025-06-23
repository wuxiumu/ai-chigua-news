<?php
namespace App\Helpers;

class Utils
{
    public static function markdownToText($md)
    {
        // 粗暴去除 markdown
        $text = preg_replace([
            '/!\[.*?\]\(.*?\)/',   // 去掉图片
            '/\[(.*?)\]\(.*?\)/',  // 去掉链接保留文本
            '/[`*_>#-]/',          // 去掉常见符号
            '/\n+/',               // 多行换行变空格
        ], [
            '', '$1', '', ' '
        ], $md);
        return trim($text);
    }
}