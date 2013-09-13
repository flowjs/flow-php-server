<?php
namespace Resumable;

class Uploader {

    /**
     * Delete chunks older than expiration time.
     * @param string $chunksFolder
     * @param int $expirationTime seconds
     * @throws Exception
     */
    public static function pruneChunks($chunksFolder, $expirationTime = 172800)
    {
        $handle = opendir($chunksFolder);
        if (!$handle) {
            throw new Exception('Failed to open folder');
        }
        while (false !== ($entry = readdir($handle))) {
            if ($entry == "." || $entry == "..") {
                continue;
            }
            $path = $chunksFolder . DIRECTORY_SEPARATOR . $entry;
            if (!is_dir($path)) {
                continue;
            }
            if (time() - filemtime($path) > $expirationTime) {
                self::deleteChunksDirectory($path);
            }
        }
        closedir($handle);
    }

    /**
     * Helper method for folder deletion
     * @param $path
     * @return bool
     */
    public static function deleteChunksDirectory($path)
    {
        foreach (scandir($path) as $item) {
            if ($item == "." || $item == "..") {
                continue;
            }
            unlink($path . DIRECTORY_SEPARATOR . $item);
        }
        return rmdir($path);
    }
}