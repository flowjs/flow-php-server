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

    function __construct($request)
    {
        if (isset($request['resumableChunkNumber'])) {
            $this->index = $request['resumableChunkNumber'];
        }
        if (isset($request['resumableCurrentChunkSize'])) {
            $this->size = $request['resumableCurrentChunkSize'];
        }
    }

    public function exists()
    {

    }
}