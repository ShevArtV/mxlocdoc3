<?php
namespace MxLocDoc\Services;

use MODX\Revolution\modX;
use MxLocDoc\MxLocDoc;

/**
 * Documentation navigation builder.
 *
 * @package mxlocdoc
 */
class NavigationBuilder
{
    /** @var modX */
    protected $modx;

    /** @var MxLocDoc */
    protected $mxlocdoc;

    /** @var PathResolver */
    protected $pathResolver;

    /** @var DocumentRepository */
    protected $documentRepository;

    public function __construct(
        modX &$modx,
        MxLocDoc $mxlocdoc,
        PathResolver $pathResolver,
        DocumentRepository $documentRepository
    ) {
        $this->modx =& $modx;
        $this->mxlocdoc = $mxlocdoc;
        $this->pathResolver = $pathResolver;
        $this->documentRepository = $documentRepository;
    }

    public function build()
    {
        $manifest = $this->loadManifest();
        if ($manifest['success']) {
            return $this->buildFromManifest($manifest);
        }

        if ($manifest['code'] !== 'file_not_found') {
            return $manifest;
        }

        return $this->buildFallback();
    }

    protected function loadManifest()
    {
        $paths = array((string)$this->mxlocdoc->config['nav_file'], 'mxlocdoc.json');
        $seen = array();

        foreach ($paths as $path) {
            $path = $this->pathResolver->normalizeDocumentPath($path);
            if ($path === '' || isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;

            $resolved = $this->pathResolver->resolveFile($path);
            if (!$resolved['success']) {
                if ($resolved['code'] === 'file_not_found') {
                    continue;
                }
                return $resolved;
            }

            $sizeCheck = $this->pathResolver->checkFileSize($resolved['path']);
            if (!$sizeCheck['success']) {
                return $sizeCheck;
            }

            $content = file_get_contents($resolved['path']);
            if ($content === false) {
                return $this->failure('file_not_readable', $this->modx->lexicon('mxlocdoc_error_file_not_readable'));
            }

            $data = json_decode($content, true);
            if (!is_array($data)) {
                return $this->failure('manifest_invalid', $this->modx->lexicon('mxlocdoc_error_navigation_manifest_invalid'));
            }

            return array(
                'success' => true,
                'path' => $resolved['relative_path'],
                'data' => $data,
            );
        }

        return $this->failure('file_not_found', $this->modx->lexicon('mxlocdoc_error_file_not_found'));
    }

    protected function buildFromManifest(array $manifest)
    {
        $items = isset($manifest['data']['items']) && is_array($manifest['data']['items'])
            ? $manifest['data']['items']
            : array();

        $builtItems = array();
        foreach ($items as $item) {
            $built = $this->buildManifestItem($item);
            if ($built === false) {
                return $this->failure('manifest_path_invalid', $this->modx->lexicon('mxlocdoc_error_navigation_manifest_path'));
            }
            if ($built !== null) {
                $builtItems[] = $built;
            }
        }

        return array(
            'success' => true,
            'source' => 'manifest',
            'manifest' => $manifest['path'],
            'title' => isset($manifest['data']['title']) ? (string)$manifest['data']['title'] : '',
            'items' => $builtItems,
        );
    }

    protected function buildManifestItem($item)
    {
        if (!is_array($item) || !empty($item['hidden'])) {
            return null;
        }

        $children = array();
        if (isset($item['items']) && is_array($item['items'])) {
            foreach ($item['items'] as $child) {
                $built = $this->buildManifestItem($child);
                if ($built === false) {
                    return false;
                }
                if ($built !== null) {
                    $children[] = $built;
                }
            }
        }

        $path = isset($item['path']) ? $this->pathResolver->normalizeDocumentPath($item['path']) : '';
        $metadata = null;
        if ($path !== '') {
            $metadata = $this->documentRepository->getMetadata($path);
            if (!$metadata['success']) {
                return false;
            }
            if (!empty($metadata['hidden'])) {
                return null;
            }
        }

        $title = isset($item['title']) ? trim((string)$item['title']) : '';
        if ($title === '' && $metadata) {
            $title = $metadata['title'];
        }

        if ($title === '' && empty($children)) {
            return null;
        }

        return array(
            'title' => $title,
            'path' => $path,
            'children' => $children,
        );
    }

    protected function buildFallback()
    {
        $documents = $this->documentRepository->listAll();
        if (!$documents['success']) {
            return $documents;
        }

        $tree = array();
        foreach ($documents['items'] as $document) {
            if (!empty($document['hidden'])) {
                continue;
            }
            $this->insertFallbackDocument($tree, $document);
        }

        $items = $this->finalizeFallbackItems($tree);

        return array(
            'success' => true,
            'source' => 'fallback',
            'manifest' => '',
            'title' => '',
            'items' => $items,
        );
    }

    protected function insertFallbackDocument(array &$tree, array $document)
    {
        $segments = explode('/', $document['path']);
        $file = array_pop($segments);
        $node =& $tree;
        $parentNode = null;

        foreach ($segments as $segment) {
            if (!isset($node[$segment])) {
                $node[$segment] = array(
                    '_title' => $this->titleFromSegment($segment),
                    '_path' => '',
                    '_order' => 0,
                    '_children' => array(),
                );
            }
            $parentNode =& $node[$segment];
            $node =& $node[$segment]['_children'];
        }

        if (preg_match('/^README\\.(md|markdown)$/i', $file) && !empty($segments)) {
            $parentNode['_title'] = $document['title'];
            $parentNode['_path'] = $document['path'];
            $parentNode['_order'] = $document['order'];
            return;
        }

        $key = preg_match('/^README\\.(md|markdown)$/i', $file) ? '__index' : $file;
        $node[$key] = array(
            '_title' => $document['title'],
            '_path' => $document['path'],
            '_order' => $document['order'],
            '_children' => array(),
        );
    }

    protected function finalizeFallbackItems(array $nodes)
    {
        $items = array();
        foreach ($nodes as $key => $node) {
            if ($key === '__index') {
                $items[] = array(
                    'title' => $node['_title'],
                    'path' => $node['_path'],
                    'children' => array(),
                    '_order' => $node['_order'],
                    '_index' => 0,
                );
                continue;
            }

            $children = $this->finalizeFallbackItems($node['_children']);
            $item = array(
                'title' => $node['_title'],
                'path' => $node['_path'],
                'children' => $children,
                '_order' => $node['_order'],
                '_index' => preg_match('/^README\\.(md|markdown)$/i', $key) ? 0 : 1,
            );
            $items[] = $item;
        }

        usort($items, array($this, 'compareFallbackItems'));

        foreach ($items as &$item) {
            unset($item['_order'], $item['_index']);
        }
        unset($item);

        return $items;
    }

    protected function compareFallbackItems($left, $right)
    {
        if ($left['_order'] !== $right['_order']) {
            return $left['_order'] < $right['_order'] ? -1 : 1;
        }

        if ($left['_index'] !== $right['_index']) {
            return $left['_index'] < $right['_index'] ? -1 : 1;
        }

        return strcasecmp($left['title'], $right['title']);
    }

    protected function titleFromSegment($segment)
    {
        return ucwords(str_replace(array('-', '_'), ' ', $segment));
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
