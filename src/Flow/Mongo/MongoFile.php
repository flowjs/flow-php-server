<?php

namespace Flow\Mongo;

use Flow\File;
use Flow\Request;
use Flow\RequestInterface;


/**
 * Notes:
 *
 * - One should ensure indices on the gridfs collection on the property 'flowIdentifier'.
 * - Chunk preprocessor not supported (must not modify chunks size)!
 * - Must use 'forceChunkSize=true' on client side.
 *
 * @package Flow
 */
class MongoFile extends File
{
    private $uploadGridFsFile;

    /**
     * @var MongoConfigInterface
     */
    private $config;

    function __construct(MongoConfigInterface $config, RequestInterface $request = null)
    {
        if ($request === null) {
            $request = new Request();
        }
        parent::__construct($config, $request);
        $this->config = $config;
    }

    /**
     * return array
     */
    protected function getGridFsFile()
    {
        if (!$this->uploadGridFsFile) {
            $gridFsFileQuery = $this->getGridFsFileQuery();
            $changed = $gridFsFileQuery;
            $changed['flowUpdated'] = new \MongoDate();
            $this->uploadGridFsFile = $this->config->getGridFs()->findAndModify($gridFsFileQuery, $changed, null,
                ['upsert' => true, 'new' => true]);
        }

        return $this->uploadGridFsFile;
    }

    /**
     * @param $index int|string 1-based
     * @return bool
     */
    public function chunkExists($index)
    {
        return $this->config->getGridFs()->chunks->find([
            'files_id' => $this->getGridFsFile()['_id'],
            'n' => (intval($index) - 1)
        ])->limit(1)->hasNext();
    }

    public function checkChunk()
    {
        return $this->chunkExists($this->request->getCurrentChunkNumber());
    }

    /**
     * Save chunk
     *
     * @return bool
     */
    public function saveChunk()
    {
        try {
            $file = $this->request->getFile();

            $chunkQuery = [
                'files_id' => $this->getGridFsFile()['_id'],
                'n' => intval($this->request->getCurrentChunkNumber()) - 1,
            ];
            $chunk = $chunkQuery;
            $data = file_get_contents($file['tmp_name']);
            $actualChunkSize = strlen($data);
            if ($actualChunkSize > $this->request->getDefaultChunkSize() ||
                ($actualChunkSize < $this->request->getDefaultChunkSize() &&
                    $this->request->getCurrentChunkNumber() != $this->request->getTotalChunks())
            ) {
                throw new \Exception("Invalid upload! (size: {$actualChunkSize})");
            }
            $chunk['data'] = new \MongoBinData($data, 0); // \MongoBinData::GENERIC is not defined for older mongo drivers
            $this->config->getGridFs()->chunks->findAndModify($chunkQuery, $chunk, [], ['upsert' => true]);
            unlink($file['tmp_name']);

            return true;
        } catch (\Exception $e) {
            try {
                if (isset($chunkQuery)) {
                    $this->config->getGridFs()->chunks->remove($chunkQuery);
                }
            } catch (\Exception $e2) {
                // fail gracefully
            }
            return false;
        }
    }

    /**
     * @return bool
     */
    public function validateFile()
    {
        $totalChunks = $this->request->getTotalChunks();

        for ($i = 1; $i <= $totalChunks; $i++) {
            if (!$this->chunkExists($i)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Merge all chunks to single file
     * @param $metadata array additional metadata for final file
     * @return \MongoId|bool of saved file or false if file was already saved
     * @throws \Exception
     */
    public function saveToGridFs($metadata = null)
    {
        $file = $this->getGridFsFile();
        $file['flowStatus'] = 'finished';
        $file['metadata'] = $metadata;
        $result = $this->config->getGridFs()->findAndModify($this->getGridFsFileQuery(), $file);
        // on second invocation no more file can be found, as the flowStatus changed:
        if (is_null($result)) {
            return false;
        } else {
            return $file['_id'];
        }
    }

    public function save($destination)
    {
        throw new \Exception("Must not use 'save' on MongoFile - use 'saveToGridFs'!");
    }

    public function deleteChunks()
    {
        // nothing to do, as chunks are directly part of the final file
    }

    /**
     * @return array
     */
    protected function getGridFsFileQuery()
    {
        return [
            'flowIdentifier' => $this->request->getIdentifier(),
            'flowStatus' => 'uploading',
            'filename' => $this->request->getFileName(),
            'chunkSize' => intval($this->request->getDefaultChunkSize()),
            'length' => intval($this->request->getTotalSize())
        ];
    }

    /**
     * Saves an uploaded file to disk. Must have been stored via {@link saveToGridFs} before.
     *
     * Note: conventional {@link \MongoGridFSFile::write} might fail on some platforms/driver versions - use this method.
     *
     * @param \MongoGridFS $gridFS
     * @param \MongoId $fileId id of grid fs file
     * @param string $fileName absolute or relative file name (existing file is deleted)
     * @return bool true on successful write and false on failure
     */
    public static function writeFile(\MongoGridFS $gridFS, \MongoId $fileId, $fileName)
    {
        if (file_exists($fileName)) {
            unlink($fileName);
        }
        $chunkEntries = $gridFS->chunks->find(['files_id' => $fileId])->sort(['n' => 1]);
        foreach ($chunkEntries as $chunkEntry) {
            /** @var \MongoBinData $data */
            $data = $chunkEntry['data'];
            if (!file_put_contents($fileName, $data->bin, FILE_APPEND)) {
                return false;
            }
        }
        return true;
    }
}