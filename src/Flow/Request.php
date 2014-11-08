<?php
namespace Flow;

class Request implements RequestInterface
{
    protected $params;
    protected $file;

    public function __construct($params = null, $file = null)
    {
        if ($params === null) {
            $params = $_REQUEST;
        }
        if ($file === null && isset($_FILES['file'])) {
            $file = $_FILES['file'];
        }
        $this->params = $params;
        $this->file = $file;
    }

    protected function getParam($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    /**
     * Get uploaded file name
     * @return string
     */
    public function getFileName()
    {
        return $this->getParam('flowFilename');
    }

    /**
     * Get total file size in bytes
     * @return int
     */
    public function getTotalSize()
    {
        return $this->getParam('flowTotalSize');
    }

    /**
     * Get file unique identifier
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getParam('flowIdentifier');
    }

    /**
     * Get file relative path
     * @return string
     */
    public function getRelativePath()
    {
        return $this->getParam('flowRelativePath');
    }

    /**
     * Get total chunks number
     * @return int
     */
    public function getTotalChunks()
    {
        return $this->getParam('flowTotalChunks');
    }

    /**
     * Get default chunk size
     * @return int
     */
    public function getDefaultChunkSize()
    {
        return $this->getParam('flowChunkSize');
    }

    /**
     * Get current uploaded chunk number, starts with 1
     * @return int
     */
    public function getCurrentChunkNumber()
    {
        return $this->getParam('flowChunkNumber');
    }

    /**
     * Get current uploaded chunk size
     * @return int
     */
    public function getCurrentChunkSize()
    {
        return $this->getParam('flowCurrentChunkSize');
    }

    /**
     * Return $_FILE request
     * @return array|null
     */
    public function getFile()
    {
        return $this->file;
    }
}
