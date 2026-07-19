<?php
namespace MxLocDoc\Processors;

use MODX\Revolution\Processors\Processor;
use MxLocDoc\MxLocDoc;

/**
 * Get Markdown document content.
 *
 * @package mxlocdoc
 * @subpackage processors
 */
class DocumentGet extends Processor
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
        $result = $this->mxlocdoc->getDocumentRepository()->get($this->getProperty('path', ''));
        if (empty($result['success'])) {
            return $this->failure($result['message'], array('code' => $result['code']));
        }

        $rendered = $this->mxlocdoc->getMarkdownRenderer()->render($result);
        if (empty($rendered['success'])) {
            return $this->failure($rendered['message'], array('code' => $rendered['code']));
        }

        $metadata = $this->mxlocdoc->getDocumentRepository()->getMetadata($result['path']);
        if (!empty($metadata['success'])) {
            $result['title'] = $metadata['title'];
        }

        $result['html'] = $rendered['html'];
        $result['assets'] = $rendered['assets'];
        $result['links'] = $rendered['links'];
        $result['warnings'] = $rendered['warnings'];

        return $this->success('', $result);
    }
}

return \MxLocDoc\Processors\DocumentGet::class;
