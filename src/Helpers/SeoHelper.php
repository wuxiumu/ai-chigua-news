<?php

namespace App\Helpers;

class SeoHelper
{
    public static function generateSeoTitle($title)
    {
        return $title. " | Your App Name";
    }
}