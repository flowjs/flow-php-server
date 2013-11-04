<?php
namespace Flow;


interface ConfigInterface {

    /**
     * Get path to temporary directory for chunks storage
     * @return string
     */
    function getTempDir();

    /**
     * Generate chunk identifier
     * @return callable
     */
    function getHashNameCallback();

    /**
     * Callback to preprocess chunk
     * @param callable $callback
     */
    function setPreprocessCallback($callback);

    /**
     * Callback to preprocess chunk
     * @return callable|null
     */
    function getPreprocessCallback();

    /**
     * Delete chunks on save
     * @param bool $delete
     */
    function setDeleteChunksOnSave($delete);

    /**
     * Delete chunks on save
     * @return bool
     */
    function getDeleteChunksOnSave();

}