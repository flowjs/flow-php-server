<?php
namespace Resumable;

class File
{
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



    function __construct($request)
    {
        if (isset($request['resumableFilename'])) {
            $this->name = $request['resumableFilename'];
        }
        if (isset($request['resumableTotalSize'])) {
            $this->size = (int) $request['resumableTotalSize'];
        }
        if (isset($request['resumableIdentifier'])) {
            $this->identifier = $request['resumableIdentifier'];
        }
        if (isset($request['resumableRelativePath'])) {
            $this->relativePath = $request['resumableRelativePath'];
        }
        if (isset($request['resumableTotalChunks'])) {
            $this->totalChunks = (int) $request['resumableTotalChunks'];
        }
        if (isset($request['resumableChunkSize'])) {
            $this->defaultChunkSize = (int) $request['resumableChunkSize'];
        }
    }

    /**
     * Get chunks dir path
     * @return string chunks dir path
     */
    public function getChunksFolder()
    {
        return $this->baseDir . '/' . $this->identifier;
    }

    /**
     * Create chunks directory if not exists
     * @param $dir
     * @param int $mode chmod mode
     * @return string chunks dir path
     */
    public function init($dir, $mode = 755)
    {
        $this->baseDir = $dir;
        $dir = $this->getChunksFolder();
        if (!file_exists($dir)) {
            mkdir($dir, $mode);
        }
        return $dir;
    }

    public function validate()
    {
        $dir = $this->getChunksFolder();
        $chunksNumber = count(array_diff(scandir($dir), array('..', '.')));
        return $chunksNumber == $this->totalChunks;
    }

    public function save()
    {

    }
}