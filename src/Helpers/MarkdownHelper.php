<?php
namespace App\Helpers;

use Parsedown;

class MarkdownHelper
{
    public static function render($text) {
        $parser = new Parsedown();
        return $parser->text($text);
    }
}