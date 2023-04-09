<?php
/**
 * @author Aleksandr Milenin
 * @email  admin@azrr.info
 * @copyright Copyright (c)Aleksandr Milenin (https://azrr.info/)
 */

namespace App\Component;

class Message
{
    public static function info(string $message): string
    {
        return $message;
    }

    public static function success(string $message): string
    {
        return "\e[0;32m$message\e[0m";
    }

    public static function warning(string $message): string
    {
        return "\e[33m$message\e[0m";
    }

    public static function error(string $message): string
    {
        return "\e[31m$message\e[0m";
    }
}
