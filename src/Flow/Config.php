<?php
namespace Flow;

class Config implements ConfigInterface
{
    private $config;

    function __construct($config = array())
    {
        $this->config = $config;
    }

    /**
     * Set path to temporary directory for chunks storage
     * @param $path
     */
    function setTempDir($path)
    {
        $this->config['tempDir'] = $path;
    }

    /**
     * Get path to temporary directory for chunks storage
     * @return string
     */
    function getTempDir()
    {
        return isset($this->config['tempDir']) ? $this->config['tempDir'] : '';
    }

    /**
     * set chunk identifier
     * @param callable $callback
     */
    function setHashNameCallback($callback)
    {
        $this->config['hashNameCallback'] = $callback;
    }

    /**
     * Generate chunk identifier
     * @return callable
     */
    function getHashNameCallback()
    {
        return isset($this->config['hashNameCallback']) ? $this->config['hashNameCallback'] :
            '\Flow\Config::hashNameCallback';
    }

    /**
     * Callback to preprocess chunk
     * @param callable $callback
     */
    function setPreprocessCallback($callback)
    {
        $this->config['preprocessCallback'] = $callback;
    }

    /**
     * Callback to preprocess chunk
     * @return callable|null
     */
    function getPreprocessCallback()
    {
        return isset($this->config['preprocessCallback']) ?
            $this->config['preprocessCallback'] :
            null;
    }

    /**
     * Delete chunks on save
     * @param bool $delete
     */
    function setDeleteChunksOnSave($delete)
    {
        $this->config['deleteChunksOnSave'] = $delete;
    }

    /**
     * Delete chunks on save
     * @return bool
     */
    function getDeleteChunksOnSave()
    {
        return isset($this->config['deleteChunksOnSave']) ?
            $this->config['deleteChunksOnSave'] :
            true;
    }

    /**
     * Generate chunk identifier
     * @param RequestInterface $request
     * @return string
     */
    public static function hashNameCallback(RequestInterface $request)
    {
        return sha1($request->getIdentifier());
    }
} 