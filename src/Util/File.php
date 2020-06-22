<?php

declare(strict_types=1);

namespace App\Util;

class File {

    /**
     * Formats bytes into a readable format e.g KB, MB, GB, TB, YB
     * 
     * @param string Byte Count
     * 
     * @return string Formatted bytes
     */
    public static function formatBytes(int $byteCount): string {
        $i = floor(log($byteCount) / log(1024));
        $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

        return sprintf('%.02F', $byteCount / pow(1024, $i)) * 1 . ' ' . $sizes[$i];
    }





    /**
     * Generate File Name
     * 
     * @param string File Extension
     * @param string Directory
     * 
     * @return string Generated File Name
     */
    public static function generateFileName(
        string $directory,
        string $fileExtension
    ): string {
        $fileName = \App\Util\Strings::generateRandomString() . '.' . $fileExtension;
        
        if(!is_dir($directory) && !file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        while(file_exists($directory.$fileName)) {
            $fileName = \App\Util\Strings::generateRandomString() . '.' . $fileExtension;
        }

        return $fileName;
    }
}