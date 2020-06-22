<?php

declare(strict_types=1);

namespace App\Util;

class Strings {

    /**
     * Get Slug
     * 
     * @param string Title
     * 
     * @return string|null Slug
     */
    public static function getSlug($text): ?string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        if(empty($text)) {
            return null;
        }

        return $text;
    }





    /**
     * Generate Random String
     *
     * @param int Length
     * @param string Type
     *
     * @return string Generated Random String
     */
    public static function generateRandomString(int $length = 8, string $type = null): string {
        $numericCharacters = "0123456789";
        $alhpabeticCharacters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        
        $characters = $numericCharacters . $alhpabeticCharacters;

        switch($type) {
            case "numeric":
                $characters = $numericCharacters;
                break;

            case "alphabetic":
                $characters = $alhpabeticCharacters;
                break;
        }

        $randomString = '';
        for($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }
}