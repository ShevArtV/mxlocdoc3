<?php
namespace MxLocDoc;

use MODX\Revolution\modX;
use MxLocDoc\Services\AssetRepository;
use MxLocDoc\Services\DocumentRepository;
use MxLocDoc\Services\MarkdownRenderer;
use MxLocDoc\Services\NavigationBuilder;
use MxLocDoc\Services\PathResolver;
use MxLocDoc\Services\SearchIndex;

/**
 * mxLocDoc service shell.
 *
 * @package mxlocdoc
 */
class MxLocDoc
{
    /** @var modX */
    public $modx;

    /** @var array */
    public $config = array();

    /** @var PathResolver */
    protected $pathResolver;

    /** @var DocumentRepository */
    protected $documentRepository;

    /** @var AssetRepository */
    protected $assetRepository;

    /** @var NavigationBuilder */
    protected $navigationBuilder;

    /** @var MarkdownRenderer */
    protected $markdownRenderer;

    /** @var SearchIndex */
    protected $searchIndex;

    public function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;

        $corePath = $this->modx->getOption(
            'mxlocdoc.core_path',
            $config,
            $this->modx->getOption('core_path') . 'components/mxlocdoc/'
        );
        $assetsUrl = $this->modx->getOption(
            'mxlocdoc.assets_url',
            $config,
            $this->modx->getOption('assets_url') . 'components/mxlocdoc/'
        );
        $assetsPath = $this->modx->getOption(
            'mxlocdoc.assets_path',
            $config,
            $this->modx->getOption('assets_path') . 'components/mxlocdoc/'
        );

        $this->config = array_merge(array(
            'core_path' => $corePath,
            'model_path' => $corePath . 'src/',
            'processors_path' => $corePath . 'processors/',
            'templates_path' => $corePath . 'templates/',
            'assets_url' => $assetsUrl,
            'assets_path' => $assetsPath,
            'connector_url' => $assetsUrl . 'connector.php',
            'docs_path' => $this->getOption('docs_path', '[[+corePath]]components/mxlocdoc/docs/'),
            'language' => $this->normalizeLanguage(isset($config['language']) ? $config['language'] : $this->modx->getOption('manager_language', null, $this->modx->getOption('cultureKey', null, 'en'))),
            'languages' => $this->getListOption('languages', array('en', 'ru')),
            'default_file' => $this->getOption('default_file', 'README.md'),
            'nav_file' => $this->getOption('nav_file', '_sidebar.json'),
            'search_enabled' => $this->getBooleanOption('search_enabled', true),
            'cache_ttl' => $this->getIntegerOption('cache_ttl', 300),
            'max_file_size' => $this->getIntegerOption('max_file_size', 1048576),
            'allowed_asset_extensions' => $this->getListOption(
                'allowed_asset_extensions',
                array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg')
            ),
        ), $config);

        if ($this->modx->lexicon) {
            $this->modx->lexicon->load('mxlocdoc:default', 'mxlocdoc:setting');
        }
    }

    /**
     * @return PathResolver
     */
    public function getPathResolver()
    {
        if (!$this->pathResolver) {
            $this->pathResolver = new PathResolver($this->modx, $this);
        }

        return $this->pathResolver;
    }

    /**
     * @return DocumentRepository
     */
    public function getDocumentRepository()
    {
        if (!$this->documentRepository) {
            $this->documentRepository = new DocumentRepository($this->modx, $this->getPathResolver());
        }

        return $this->documentRepository;
    }

    /**
     * @return AssetRepository
     */
    public function getAssetRepository()
    {
        if (!$this->assetRepository) {
            $this->assetRepository = new AssetRepository($this->modx, $this, $this->getPathResolver());
        }

        return $this->assetRepository;
    }

    /**
     * @return NavigationBuilder
     */
    public function getNavigationBuilder()
    {
        if (!$this->navigationBuilder) {
            $this->navigationBuilder = new NavigationBuilder($this->modx, $this, $this->getPathResolver(), $this->getDocumentRepository());
        }

        return $this->navigationBuilder;
    }

    /**
     * @return MarkdownRenderer
     */
    public function getMarkdownRenderer()
    {
        if (!$this->markdownRenderer) {
            $this->markdownRenderer = new MarkdownRenderer($this->modx, $this, $this->getAssetRepository());
        }

        return $this->markdownRenderer;
    }

    /**
     * @return SearchIndex
     */
    public function getSearchIndex()
    {
        if (!$this->searchIndex) {
            $this->searchIndex = new SearchIndex($this->modx, $this, $this->getDocumentRepository());
        }

        return $this->searchIndex;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return $this->modx->getOption('mxlocdoc.' . $key, null, $default);
    }

    public function setLanguage($language)
    {
        $language = $this->normalizeLanguage($language);
        if ($language === $this->config['language']) {
            return;
        }

        $this->config['language'] = $language;
        $this->pathResolver = null;
        $this->documentRepository = null;
        $this->assetRepository = null;
        $this->navigationBuilder = null;
        $this->markdownRenderer = null;
        $this->searchIndex = null;
    }

    public function getLanguage()
    {
        return $this->config['language'];
    }

    public function normalizeLanguage($language)
    {
        $language = strtolower(trim((string)$language));
        $language = str_replace('_', '-', $language);
        return preg_match('/^[a-z]{2}(?:-[a-z0-9]{2,8})?$/', $language) ? $language : '';
    }

    public function clearCache()
    {
        $cachePath = $this->getCachePath();
        if (!is_dir($cachePath)) {
            return true;
        }

        return $this->modx->cacheManager->deleteTree($cachePath, array(
            'deleteTop' => false,
            'skipDirs' => false,
            'extensions' => array('.cache', '.cache.php'),
        ));
    }

    public function getCachePath()
    {
        return rtrim($this->modx->getOption('cache_path'), '/\\') . DIRECTORY_SEPARATOR . 'mxlocdoc' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $key
     * @param bool $default
     * @return bool
     */
    public function getBooleanOption($key, $default = false)
    {
        $value = $this->getOption($key, $default ? '1' : '0');
        return in_array(strtolower((string)$value), array('1', 'true', 'yes', 'on'), true);
    }

    /**
     * @param string $key
     * @param int $default
     * @return int
     */
    public function getIntegerOption($key, $default = 0)
    {
        $value = $this->getOption($key, $default);
        return is_numeric($value) ? (int)$value : (int)$default;
    }

    /**
     * @param string $key
     * @param array $default
     * @return array
     */
    public function getListOption($key, array $default = array())
    {
        $value = $this->getOption($key, implode(',', $default));
        if (is_array($value)) {
            $items = $value;
        } else {
            $items = explode(',', (string)$value);
        }

        $result = array();
        foreach ($items as $item) {
            $item = strtolower(trim((string)$item));
            if ($item !== '' && !in_array($item, $result, true)) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
