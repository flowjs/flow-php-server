<?php
namespace Flow;

/**
 * Class FustyRequest
 * Imitates single file request as a single chunk file upload
 * @package Flow
 */
class FustyRequest implements RequestInterface {

    private $params;
    private $file;

    function __construct($params = null, $file = null)
    {
        if ($params === null) {
            $params = $_REQUEST;
        }
        if ($file === null && isset($_FILES['file'])) {
            $file = $_FILES['file'];
        }
        $this->params = $params;
        $this->file = $file;
        if ($this->isFustyFlowRequest()) {
            $this->params['flowTotalSize'] = isset($file['size']) ? $file['size'] : 0;
            $this->params['flowTotalChunks'] = 1;
            $this->params['flowChunkNumber'] = 1;
            $this->params['flowChunkSize'] = $this->params['flowTotalSize'];
            $this->params['flowCurrentChunkSize'] = $this->params['flowTotalSize'];
        }
    }

    private function getParam($name)
    {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    /**
     * Checks if request is formed by fusty flow
     * @return bool
     */
    function isFustyFlowRequest()
    {
        return !$this->getTotalSize() && $this->getFileName() && $this->getFile();
    }

    /**
     * Get uploaded file name
     * @return string
     */
    function getFileName()
    {
        return $this->getParam('flowFilename');
    }

    /**
     * Get total file size in bytes
     * @return int
     */
    function getTotalSize()
    {
        return $this->getParam('flowTotalSize');
    }

    /**
     * Get file unique identifier
     * @return string
     */
    function getIdentifier()
    {
        return $this->getParam('flowIdentifier');
    }

    /**
     * Get file relative path
     * @return string
     */
    function getRelativePath()
    {
        return $this->getParam('flowRelativePath');
    }

    /**
     * Get total chunks number
     * @return int
     */
    function getTotalChunks()
    {
        return $this->getParam('flowTotalChunks');
    }

    /**
     * Get default chunk size
     * @return int
     */
    function getDefaultChunkSize()
    {
        return $this->getParam('flowChunkSize');
    }

    /**
     * Get current uploaded chunk number, starts with 1
     * @return int
     */
    function getCurrentChunkNumber()
    {
        return $this->getParam('flowChunkNumber');
    }

    /**
     * Get current uploaded chunk size
     * @return int
     */
    function getCurrentChunkSize()
    {
        return $this->getParam('flowCurrentChunkSize');
    }

    /**
     * Return $_FILE request
     * @return array|null
     */
    function getFile()
    {
        return $this->file;
    }

} 