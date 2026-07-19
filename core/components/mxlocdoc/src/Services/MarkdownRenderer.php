<?php
namespace MxLocDoc\Services;

use MODX\Revolution\modX;
use MxLocDoc\MxLocDoc;

/**
 * Safe Markdown renderer for manager documentation.
 *
 * @package mxlocdoc
 */
class MarkdownRenderer
{
    /** @var modX */
    protected $modx;

    /** @var MxLocDoc */
    protected $mxlocdoc;

    /** @var AssetRepository */
    protected $assetRepository;

    /** @var \Parsedown */
    protected $parser;

    public function __construct(modX &$modx, MxLocDoc $mxlocdoc, AssetRepository $assetRepository)
    {
        $this->modx =& $modx;
        $this->mxlocdoc = $mxlocdoc;
        $this->assetRepository = $assetRepository;
    }

    public function render(array $document)
    {
        $parser = $this->getParser();
        if (!$parser) {
            return $this->failure('renderer_unavailable', $this->modx->lexicon('mxlocdoc_error_renderer_unavailable'));
        }

        $html = $parser->text($this->stripFrontMatter($document['content']));
        $rewrite = $this->rewriteReferences($html, $document['path']);

        return array(
            'success' => true,
            'html' => $rewrite['html'],
            'assets' => $rewrite['assets'],
            'links' => $rewrite['links'],
            'warnings' => $rewrite['warnings'],
        );
    }

    protected function getParser()
    {
        if ($this->parser) {
            return $this->parser;
        }

        $this->parser = new \Parsedown();
        if (method_exists($this->parser, 'setSafeMode')) {
            $this->parser->setSafeMode(true);
        }

        return $this->parser;
    }

    protected function stripFrontMatter($content)
    {
        $content = (string)$content;
        if (!preg_match('/^---\R.*?\R---\R/s', ltrim($content))) {
            return $content;
        }

        return preg_replace('/^---\R.*?\R---\R/s', '', ltrim($content), 1);
    }

    protected function rewriteReferences($html, $documentPath)
    {
        $result = array(
            'html' => (string)$html,
            'assets' => array(),
            'links' => array(),
            'warnings' => array(),
        );

        if (trim($html) === '' || !class_exists('DOMDocument')) {
            return $result;
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $loaded = $dom->loadHTML(
            '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><div id="mxlocdoc-fragment">' . $html . '</div></body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return $result;
        }

        $container = $dom->getElementById('mxlocdoc-fragment');
        if (!$container) {
            return $result;
        }

        $images = $container->getElementsByTagName('img');
        for ($i = 0; $i < $images->length; $i++) {
            $this->rewriteImage($images->item($i), $documentPath, $result);
        }

        $links = $container->getElementsByTagName('a');
        for ($i = 0; $i < $links->length; $i++) {
            $this->rewriteLink($links->item($i), $documentPath, $result);
        }

        $result['html'] = $this->innerHtml($container);
        return $result;
    }

    protected function rewriteImage(\DOMElement $image, $documentPath, array &$result)
    {
        $src = $image->getAttribute('src');
        if ($src === '' || $this->isExternalReference($src)) {
            return;
        }

        $reference = $this->splitReference($src);
        $path = $this->resolveRelativePath($documentPath, $reference['path']);
        if ($path === '') {
            $this->markBrokenAsset($image, 'path_invalid');
            $result['warnings'][] = array('type' => 'asset', 'path' => $src, 'code' => 'path_invalid');
            return;
        }

        $asset = $this->assetRepository->get($path);
        if (empty($asset['success'])) {
            $this->markBrokenAsset($image, $asset['code']);
            $result['assets'][] = array('path' => $path, 'success' => false, 'code' => $asset['code']);
            $result['warnings'][] = array('type' => 'asset', 'path' => $path, 'code' => $asset['code']);
            $this->modx->log(modX::LOG_LEVEL_WARN, '[mxLocDoc] Asset reference failed: ' . $path . ' (' . $asset['code'] . ')');
            return;
        }

        $image->setAttribute('src', $this->buildAssetUrl($path));
        $image->setAttribute('data-mxlocdoc-asset-path', $path);
        $result['assets'][] = array('path' => $path, 'success' => true);
    }

    protected function rewriteLink(\DOMElement $link, $documentPath, array &$result)
    {
        $href = $link->getAttribute('href');
        if ($href === '' || $this->isExternalReference($href)) {
            return;
        }

        $reference = $this->splitReference($href);
        if (!$this->isMarkdownPath($reference['path'])) {
            return;
        }

        $path = $this->resolveRelativePath($documentPath, $reference['path']);
        if ($path === '') {
            $result['warnings'][] = array('type' => 'link', 'path' => $href, 'code' => 'path_invalid');
            return;
        }

        $link->setAttribute('href', '#' . $path);
        $link->setAttribute('data-mxlocdoc-path', $path);
        if ($reference['fragment'] !== '') {
            $link->setAttribute('data-mxlocdoc-anchor', ltrim($reference['fragment'], '#'));
        }

        $result['links'][] = array('path' => $path, 'anchor' => ltrim($reference['fragment'], '#'));
    }

    protected function buildAssetUrl($path)
    {
        $url = $this->mxlocdoc->config['connector_url']
            . '?action=mgr/asset/get&path=' . rawurlencode($path);
        $auth = isset($_REQUEST['HTTP_MODAUTH']) ? trim((string)$_REQUEST['HTTP_MODAUTH']) : '';

        if ($auth !== '') {
            $url .= '&HTTP_MODAUTH=' . rawurlencode($auth);
        }
        if (!empty($this->mxlocdoc->config['language'])) {
            $url .= '&language=' . rawurlencode($this->mxlocdoc->config['language']);
        }

        return $url;
    }

    protected function splitReference($reference)
    {
        $path = (string)$reference;
        $query = '';
        $fragment = '';

        $hashPosition = strpos($path, '#');
        if ($hashPosition !== false) {
            $fragment = substr($path, $hashPosition);
            $path = substr($path, 0, $hashPosition);
        }

        $queryPosition = strpos($path, '?');
        if ($queryPosition !== false) {
            $query = substr($path, $queryPosition);
            $path = substr($path, 0, $queryPosition);
        }

        return array(
            'path' => $path,
            'query' => $query,
            'fragment' => $fragment,
        );
    }

    protected function isExternalReference($reference)
    {
        $reference = trim((string)$reference);
        return $reference === ''
            || strpos($reference, '#') === 0
            || strpos($reference, '//') === 0
            || preg_match('#^[a-z][a-z0-9+.-]*:#i', $reference);
    }

    protected function isMarkdownPath($path)
    {
        return (bool)preg_match('/\.(md|markdown)$/i', (string)$path);
    }

    protected function resolveRelativePath($documentPath, $target)
    {
        $target = str_replace('\\', '/', trim((string)$target));
        if ($target === '') {
            return '';
        }

        $base = dirname(str_replace('\\', '/', (string)$documentPath));
        $combined = ($base === '.' || $base === '') ? $target : $base . '/' . $target;
        if (strpos($target, '/') === 0) {
            $combined = ltrim($target, '/');
        }

        $segments = array();
        foreach (explode('/', $combined) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                if (empty($segments)) {
                    return '';
                }
                array_pop($segments);
                continue;
            }
            $segments[] = $segment;
        }

        return implode('/', $segments);
    }

    protected function markBrokenAsset(\DOMElement $image, $code)
    {
        $class = trim($image->getAttribute('class') . ' mxlocdoc-asset-missing');
        $image->setAttribute('class', $class);
        $image->setAttribute('data-mxlocdoc-asset-error', $code);
    }

    protected function innerHtml(\DOMElement $element)
    {
        $html = '';
        foreach ($element->childNodes as $child) {
            $html .= $element->ownerDocument->saveHTML($child);
        }

        return $html;
    }

    protected function failure($code, $message)
    {
        return array(
            'success' => false,
            'code' => $code,
            'message' => $message,
        );
    }
}
