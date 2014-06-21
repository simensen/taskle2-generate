<?php
namespace Taskle\Generate\Helper;

class CaseHelper
{
    public static function camelCase($value)
    {
        return lcfirst(self::studlyCase($value));
    }

    public static function studlyCase($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $chunks    = explode('-', $value);
        $ucfirsted = array_map(function($s) { return ucfirst($s); }, $chunks);

        return implode('', $ucfirsted);
    }
}
