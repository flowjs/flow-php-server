<?php
namespace Resumable;

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
        if (isset($request['resumableChunkNumber'])) {
            $this->index = (int) $request['resumableChunkNumber'];
        }
        if (isset($request['resumableCurrentChunkSize'])) {
            $this->size = (int) $request['resumableCurrentChunkSize'];
        }
        $this->prefix = $prefix;
    }

    /**
     *
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
        return move_uploaded_file($file['tmp_name'], $this->getChunkPath($dir));
    }

    /**
     * Overwrite file if it exists
     * @param string $file
     * @param string $dir file directory
     * @return bool
     */
    public function overwrite($file, $dir)
    {
        if ($this->exists($dir)) {
            $this->delete($dir);
        }
        return $this->save($file, $dir);
    }
}