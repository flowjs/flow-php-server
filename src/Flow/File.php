<?php
namespace Flow;

class File
{
    /**
     * Generate chunks folder name
     * @param File $file
     * @return string
     */
    public static function hashNameCallback($file)
    {
        return sha1($file->identifier);
    }

    /**
     * File name
     * @var string
     */
    public $name;

    /**
     * File size
     * @var int
     */
    public $size;

    /**
     * File identifier
     * @var string
     */
    public $identifier;

    /**
     * File relative path
     * @var string
     */
    public $relativePath;

    /**
     * Chunks number
     * @var int
     */
    public $totalChunks;

    /**
     * Default chunk size
     * @var int
     */
    public $defaultChunkSize;

    /**
     * Path to base dir
     * @var string
     */
    public $baseDir;

    /**
     * Path to file chunks dir
     * @var string
     */
    public $chunksDir;

    /**
     * Chunk name prefix
     * @var string
     */
    public $prefix;

    function __construct($request, $prefix = '')
    {
        if (isset($request['flowFilename'])) {
            $this->name = $request['flowFilename'];
        }
        if (isset($request['flowTotalSize'])) {
            $this->size = (int) $request['flowTotalSize'];
        }
        if (isset($request['flowIdentifier'])) {
            $this->identifier = $request['flowIdentifier'];
        }
        if (isset($request['flowRelativePath'])) {
            $this->relativePath = $request['flowRelativePath'];
        }
        if (isset($request['flowTotalChunks'])) {
            $this->totalChunks = (int) $request['flowTotalChunks'];
        }
        if (isset($request['flowChunkSize'])) {
            $this->defaultChunkSize = (int) $request['flowChunkSize'];
        }
        $this->prefix = $prefix;
    }

    /**
     * Get chunks dir path
     * @return string chunks dir path
     */
    public function getChunksDirPath()
    {
        return $this->baseDir . DIRECTORY_SEPARATOR . $this->chunksDir;
    }

    /**
     * Create chunks directory if not exists
     * @param $dir
     * @param int $mode chmod mode
     * @param callable $hashNameCallback callback for generating unique chunks folder name,
     * first argument stands for \Flow\File.
     * @return string chunks dir path
     */
    public function init($dir, $mode = 0755, $hashNameCallback = '\Flow\File::hashNameCallback')
    {
        $this->baseDir = $dir;
        $this->chunksDir = call_user_func($hashNameCallback, $this);
        $dir = $this->getChunksDirPath();
        if (!file_exists($dir)) {
            mkdir($dir, $mode);
        }
        return $dir;
    }

    /**
     * Check if file upload is complete
     * @return bool
     */
    public function validate()
    {
        $dir = $this->getChunksDirPath();
        $totalChunksSize = 0;
        for ($i = 1; $i <= $this->totalChunks; $i++) {
            $file = $dir . DIRECTORY_SEPARATOR . $this->prefix . $i;
            if (!file_exists($file)) {
                return false;
            }
            $totalChunksSize += filesize($file);
        }
        return $this->size == $totalChunksSize;
    }

    /**
     * Merge all chunks to single file
     * @param string $destination final file location
     * @param callable $preProcessChunk function for pre processing chunk
     * @param bool $deleteChunks indicates if chunks folder will be deleted after save
     * @return bool indicates if file was saved
     * @throws \Exception
     * @throws Exception
     */
    public function save($destination, $preProcessChunk = null, $deleteChunks = true)
    {
        $fh = fopen($destination, 'wb');
        if (!$fh) {
            throw new Exception('Failed to open destination file');
        }
        if (!flock($fh, LOCK_EX | LOCK_NB, $blocked)) {
            if ($blocked) {
                // Concurrent request has requested a lock.
                // File is being processed at the moment.
                // Warning: lock is not checked in windows.
                return false;
            }
            throw new Exception('Failed to lock file');
        }
        $dir = $this->getChunksDirPath();
        try {
            for ($i = 1; $i <= $this->totalChunks; $i++) {
                $file = $dir . DIRECTORY_SEPARATOR . $this->prefix . $i;
                $chunk = fopen($file, "rb");
                if (!$chunk) {
                    throw new Exception('Failed to open chunk');
                }
                if ($preProcessChunk !== null) {
                    call_user_func($preProcessChunk, $chunk);
                }
                stream_copy_to_stream($chunk, $fh);
                fclose($chunk);
            }
        } catch (\Exception $e) {
            flock($fh, LOCK_UN);
            fclose($fh);
            throw $e;
        }
        if ($deleteChunks) {
            $this->deleteChunks();
        }
        flock($fh, LOCK_UN);
        fclose($fh);
        return true;
    }

    /**
     * Delete chunks dir
     * @return bool
     */
    public function deleteChunks()
    {
        return Uploader::deleteChunksDirectory($this->getChunksDirPath());
    }
}