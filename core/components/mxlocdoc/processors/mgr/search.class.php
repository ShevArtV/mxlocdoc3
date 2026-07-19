<?php
namespace MxLocDoc\Processors;

use MODX\Revolution\Processors\Processor;
use MxLocDoc\MxLocDoc;

/**
 * Search Markdown documentation.
 *
 * @package mxlocdoc
 * @subpackage processors
 */
class Search extends Processor
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
        $result = $this->mxlocdoc->getSearchIndex()->search(
            $this->getProperty('query', ''),
            $this->getProperty('limit', 20)
        );
        if (empty($result['success'])) {
            return $this->failure($result['message'], array('code' => $result['code']));
        }

        return $this->success('', $result);
    }
}

return \MxLocDoc\Processors\Search::class;
