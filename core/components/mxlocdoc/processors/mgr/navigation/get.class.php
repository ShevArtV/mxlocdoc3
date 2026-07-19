<?php
namespace MxLocDoc\Processors;

use MODX\Revolution\Processors\Processor;
use MxLocDoc\MxLocDoc;

/**
 * Get documentation navigation.
 *
 * @package mxlocdoc
 * @subpackage processors
 */
class NavigationGet extends Processor
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
        $result = $this->mxlocdoc->getNavigationBuilder()->build();
        if (empty($result['success'])) {
            return $this->failure($result['message'], array('code' => $result['code']));
        }

        $languageContext = $this->mxlocdoc->getPathResolver()->getLanguageContext();
        if (!empty($languageContext['success'])) {
            $result['language'] = $languageContext['language'];
            $result['languages'] = $languageContext['languages'];
            $result['is_multilingual'] = $languageContext['is_multilingual'];
        }

        return $this->success('', $result);
    }
}

return \MxLocDoc\Processors\NavigationGet::class;
