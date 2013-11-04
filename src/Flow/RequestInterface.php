<?php
namespace Flow;

interface RequestInterface {

    /**
     * Get uploaded file name
     * @return string
     */
    function getFileName();

    /**
     * Get total file size in bytes
     * @return int
     */
    function getTotalSize();

    /**
     * Get file unique identifier
     * @return string
     */
    function getIdentifier();

    /**
     * Get file relative path
     * @return string
     */
    function getRelativePath();

    /**
     * Get total chunks number
     * @return int
     */
    function getTotalChunks();

    /**
     * Get default chunk size
     * @return int
     */
    function getDefaultChunkSize();

    /**
     * Get current uploaded chunk number, starts with 1
     * @return int
     */
    function getCurrentChunkNumber();

    /**
     * Get current uploaded chunk size
     * @return int
     */
    function getCurrentChunkSize();

} 