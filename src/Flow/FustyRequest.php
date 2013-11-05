<?php
namespace Flow;

/**
 * Class FustyRequest
 * Imitates single file request as a single chunk file upload
 * @package Flow
 */
class FustyRequest extends Request
{

    function __construct($params = null, $file = null)
    {
        parent::__construct($params, $file);
        if ($this->isFustyFlowRequest()) {
            $this->params['flowTotalSize'] = isset($file['size']) ? $file['size'] : 0;
            $this->params['flowTotalChunks'] = 1;
            $this->params['flowChunkNumber'] = 1;
            $this->params['flowChunkSize'] = $this->params['flowTotalSize'];
            $this->params['flowCurrentChunkSize'] = $this->params['flowTotalSize'];
        }
    }

    /**
     * Checks if request is formed by fusty flow
     * @return bool
     */
    function isFustyFlowRequest()
    {
        return !$this->getTotalSize() && $this->getFileName() && $this->getFile();
    }
} 