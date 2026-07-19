<?php
namespace MxLocDoc\Processors;

use MODX\Revolution\Processors\Processor;
use MxLocDoc\MxLocDoc;

/**
 * Stream a protected documentation asset.
 *
 * @package mxlocdoc
 * @subpackage processors
 */
class AssetGet extends Processor
{
    /** @var MxLocDoc */
    protected $mxlocdoc;

    public $languageTopics = array('mxlocdoc:default');

    public function initialize()
    {
        $this->mxlocdoc = $this->modx->services->has('mxlocdoc')
            ? $this->modx->services->get('mxlocdoc')
            : new MxLocDoc($this->modx);

        if (!$this->mxlocdoc) {
            return $this->modx->lexicon('mxlocdoc_error_service_unavailable');
        }
        $this->mxlocdoc->setLanguage($this->getProperty('language', ''));

        return parent::initialize();
    }

    public function process()
    {
        $result = $this->mxlocdoc->getAssetRepository()->get($this->getProperty('path', ''));
        if (empty($result['success'])) {
            return $this->failure($result['message'], array('code' => $result['code']));
        }

        @session_write_close();
        header('Content-Type: ' . $result['mime']);
        header('Content-Length: ' . $result['size']);
        header('Content-Disposition: inline; filename="' . str_replace('"', '', $result['name']) . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($result['path']);
        exit;
    }
}

return \MxLocDoc\Processors\AssetGet::class;
