<?php

final class UrlConverter
{
    public static function Slugify($string)
    {
        if ($string !== null && trim($string)) {
            $map = [
                'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ö' => 'o', 'ő' => 'o',
                'ú' => 'u', 'ü' => 'u', 'ű' => 'u',
                'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ö' => 'o', 'Ő' => 'o',
                'Ú' => 'u', 'Ü' => 'u', 'Ű' => 'u'
            ];
            $string = strtr($string, $map);
            $string = strtolower($string);
            $string = preg_replace('/[^a-z0-9]+/', '-', $string);
            return trim($string, '-');
        }
        throw new UrlException("Could not slugilfy URL.");
    }
}
