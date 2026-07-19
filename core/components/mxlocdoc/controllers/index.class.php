<?php
/**
 * mxLocDoc manager controller.
 *
 * @package mxlocdoc
 */
class mxLocDocIndexManagerController extends \MODX\Revolution\modExtraManagerController
{
    /** @var \MxLocDoc\MxLocDoc */
    public $mxlocdoc;

    public function initialize()
    {
        $this->mxlocdoc = $this->modx->services->has('mxlocdoc')
            ? $this->modx->services->get('mxlocdoc')
            : new \MxLocDoc\MxLocDoc($this->modx);

        parent::initialize();
    }

    public function getLanguageTopics()
    {
        return array('mxlocdoc:default');
    }

    public function checkPermissions()
    {
        return true;
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('mxlocdoc');
    }

    public function loadCustomCssJs()
    {
        if (!$this->mxlocdoc) {
            return;
        }

        $assetsUrl = $this->mxlocdoc->config['assets_url'];
        $assetsPath = $this->mxlocdoc->config['assets_path'];
        $languageContext = $this->mxlocdoc->getPathResolver()->getLanguageContext();
        if (empty($languageContext['success'])) {
            $languageContext = array('language' => '', 'languages' => array(), 'is_multilingual' => false);
        }

        $version = function ($relativePath) use ($assetsPath) {
            $file = $assetsPath . $relativePath;
            return is_file($file) ? '?v=' . filemtime($file) : '';
        };

        $this->addCss($assetsUrl . 'css/mgr/main.css' . $version('css/mgr/main.css'));
        $this->addJavascript($assetsUrl . 'js/mgr/mxlocdoc.js' . $version('js/mgr/mxlocdoc.js'));
        $this->addHtml('<script type="text/javascript">
            MxLocDoc = window.MxLocDoc || {};
            MxLocDoc.config = ' . $this->modx->toJSON(array(
                'connector_url' => $this->mxlocdoc->config['connector_url'],
                'assets_url' => $assetsUrl,
                'default_file' => $this->mxlocdoc->config['default_file'],
                'language' => $languageContext['language'],
                'languages' => $languageContext['languages'],
                'is_multilingual' => $languageContext['is_multilingual'],
                'lexicon' => array(
                    'title' => $this->modx->lexicon('mxlocdoc'),
                    'navigation' => $this->modx->lexicon('mxlocdoc_navigation'),
                    'open_navigation' => $this->modx->lexicon('mxlocdoc_open_navigation'),
                    'close_navigation' => $this->modx->lexicon('mxlocdoc_close_navigation'),
                    'search_placeholder' => $this->modx->lexicon('mxlocdoc_search_placeholder'),
                    'search_hint' => $this->modx->lexicon('mxlocdoc_search_coming_soon'),
                    'search_empty' => $this->modx->lexicon('mxlocdoc_search_empty'),
                    'search_error' => $this->modx->lexicon('mxlocdoc_search_error'),
                    'loading' => $this->modx->lexicon('mxlocdoc_loading'),
                    'breadcrumbs' => $this->modx->lexicon('mxlocdoc_breadcrumbs'),
                    'toc' => $this->modx->lexicon('mxlocdoc_toc'),
                    'loading_navigation' => $this->modx->lexicon('mxlocdoc_loading_navigation'),
                    'loading_document' => $this->modx->lexicon('mxlocdoc_loading_document'),
                    'navigation_error' => $this->modx->lexicon('mxlocdoc_navigation_error'),
                    'document_error' => $this->modx->lexicon('mxlocdoc_document_error'),
                    'documents_empty' => $this->modx->lexicon('mxlocdoc_documents_empty'),
                    'documentation' => $this->modx->lexicon('mxlocdoc_documentation'),
                    'invalid_json' => $this->modx->lexicon('mxlocdoc_invalid_json'),
                ),
            )) . ';
        </script>');
    }

    public function getTemplateFile()
    {
        return $this->mxlocdoc->config['templates_path'] . 'home.tpl';
    }
}
