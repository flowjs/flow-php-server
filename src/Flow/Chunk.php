<?php
namespace Flow;

class Chunk {

    /**
     * Chunk index
     * @var int
     */
    public $index;

    /**
     * Chunk size
     * @var int
     */
    public $size;

    /**
     * Chunk file prefix
     * @var string
     */
    public $prefix;

    function __construct($request, $prefix = '')
    {
        if (isset($request['flowChunkNumber'])) {
            $this->index = (int) $request['flowChunkNumber'];
        }
        if (isset($request['flowCurrentChunkSize'])) {
            $this->size = (int) $request['flowCurrentChunkSize'];
        }
        $this->prefix = $prefix;
    }

    /**
     * Get path to the chunk
     * @param $dir
     * @return string
     */
    public function getChunkPath($dir)
    {
        return $dir . DIRECTORY_SEPARATOR . $this->prefix . $this->index;
    }

    /**
     * Check if chunk exist
     * @param string $dir file directory
     * @return bool
     */
    public function exists($dir)
    {
        return file_exists($this->getChunkPath($dir));
    }

    /**
     * Validate file request
     * @param array $file pass the following $_FILES['file'] as a value
     * @return bool
     * @throws exception for invalid $file array
     */
    public function validate($file)
    {
        if (!isset($file['tmp_name']) || !isset($file['size']) || !isset($file['error'])) {
            throw new Exception('Invalid $_FILE params');
        }
        if ($this->size != $file['size']) {
            return false;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        return true;
    }

    /**
     * Delete chunk
     * @param string $dir file directory
     * @return bool
     */
    public function delete($dir)
    {
        return unlink($this->getChunkPath($dir));
    }

    /**
     * Save file
     * @param array $file pass the following $_FILES['file'] as a value
     * @param string $dir file directory
     * @return bool
     */
    public function save($file, $dir)
    {
        return $this->move_uploaded_file($file['tmp_name'], $this->getChunkPath($dir));
    }

    /**
     * Helper method for testing
     * @param $filename
     * @param $destination
     * @return bool
     */
    protected function move_uploaded_file($filename, $destination)
    {
        return move_uploaded_file($filename, $destination);
    }
}