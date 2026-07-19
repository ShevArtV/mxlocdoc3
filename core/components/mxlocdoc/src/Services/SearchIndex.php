<?php
namespace MxLocDoc\Services;

use MODX\Revolution\modX;
use MxLocDoc\MxLocDoc;

/**
 * Filesystem Markdown search for manager documentation.
 *
 * @package mxlocdoc
 */
class SearchIndex
{
    /** @var modX */
    protected $modx;

    /** @var MxLocDoc */
    protected $mxlocdoc;

    /** @var DocumentRepository */
    protected $documentRepository;

    public function __construct(modX &$modx, MxLocDoc $mxlocdoc, DocumentRepository $documentRepository)
    {
        $this->modx =& $modx;
        $this->mxlocdoc = $mxlocdoc;
        $this->documentRepository = $documentRepository;
    }

    public function search($query, $limit = 20)
    {
        if (empty($this->mxlocdoc->config['search_enabled'])) {
            return $this->failure('search_disabled', $this->modx->lexicon('mxlocdoc_error_search_disabled'));
        }

        $query = $this->normalizeQuery($query);
        $limit = max(1, min(50, (int)$limit));
        if ($query === '') {
            return array('success' => true, 'query' => '', 'items' => array(), 'total' => 0);
        }

        $documents = $this->getIndexedDocuments();
        if (empty($documents['success'])) {
            return $documents;
        }

        $items = array();
        foreach ($documents['items'] as $metadata) {
            if (!empty($metadata['hidden'])) {
                continue;
            }

            $plain = isset($metadata['plain']) ? $metadata['plain'] : '';
            $score = $this->score($query, $metadata, $plain);
            if ($score <= 0) {
                continue;
            }

            $items[] = array(
                'title' => $metadata['title'],
                'path' => $metadata['path'],
                'snippet' => $this->snippet($plain, $query),
                'score' => $score,
            );
        }

        usort($items, array($this, 'compareResults'));
        $total = count($items);
        $items = array_slice($items, 0, $limit);

        return array(
            'success' => true,
            'query' => $query,
            'items' => $items,
            'total' => $total,
            'cached' => !empty($documents['cached']),
        );
    }

    protected function getIndexedDocuments()
    {
        $ttl = (int)$this->mxlocdoc->config['cache_ttl'];
        if ($ttl <= 0) {
            return $this->buildIndex();
        }

        $cacheFile = $this->getCacheFile();
        if ($cacheFile === '') {
            return $this->buildIndex();
        }

        if (is_file($cacheFile) && filemtime($cacheFile) + $ttl >= time()) {
            $cached = $this->readCacheFile($cacheFile);
            if (is_array($cached) && !empty($cached['success']) && isset($cached['items'])) {
                $cached['cached'] = true;
                return $cached;
            }
        }

        $index = $this->buildIndex();
        if (!empty($index['success'])) {
            $this->writeCacheFile($cacheFile, $index);
        }

        return $index;
    }

    protected function buildIndex()
    {
        $documents = $this->documentRepository->listAll();
        if (empty($documents['success'])) {
            return $documents;
        }

        $items = array();
        foreach ($documents['items'] as $metadata) {
            if (!empty($metadata['hidden'])) {
                continue;
            }

            $document = $this->documentRepository->get($metadata['path']);
            if (empty($document['success'])) {
                continue;
            }

            $metadata['plain'] = $this->plainText($document['content']);
            $items[] = $metadata;
        }

        return array(
            'success' => true,
            'items' => $items,
            'cached' => false,
            'language' => $this->mxlocdoc->config['language'],
        );
    }

    protected function getCacheFile()
    {
        $root = $this->mxlocdoc->getPathResolver()->getRootPath();
        if (empty($root['success'])) {
            return '';
        }

        return $this->getCacheDirectory() . 'search-' . md5($root['path'] . '|' . $this->mxlocdoc->config['language']) . '.cache.php';
    }

    protected function getCacheDirectory()
    {
        return $this->mxlocdoc->getCachePath();
    }

    protected function writeCacheFile($cacheFile, array $index)
    {
        $directory = dirname($cacheFile);
        if (!is_dir($directory)) {
            @mkdir($directory, 0777, true);
        }
        if (!is_dir($directory) || !is_writable($directory)) {
            return;
        }

        @file_put_contents($cacheFile, '<?php return ' . var_export($index, true) . ';' . "\n", LOCK_EX);
    }

    protected function readCacheFile($cacheFile)
    {
        try {
            $data = @include $cacheFile;
        } catch (\Throwable $e) {
            return null;
        }

        return is_array($data) ? $data : null;
    }

    protected function score($query, array $metadata, $plain)
    {
        $score = 0;
        $title = $this->lower($metadata['title']);
        $path = $this->lower($metadata['path']);
        $body = $this->lower($plain);

        if (strpos($title, $query) !== false) {
            $score += 30;
        }
        if (strpos($path, $query) !== false) {
            $score += 15;
        }
        if (strpos($body, $query) !== false) {
            $score += 5;
        }

        return $score;
    }

    protected function snippet($plain, $query)
    {
        $plain = preg_replace('/\s+/u', ' ', trim((string)$plain));
        if ($plain === '') {
            return '';
        }

        $lower = $this->lower($plain);
        $position = $this->indexOf($lower, $query);
        if ($position === false) {
            return $this->cut($plain, 0, 180);
        }

        $start = max(0, $position - 70);
        $snippet = $this->cut($plain, $start, 180);

        return ($start > 0 ? '...' : '') . $snippet . ($this->length($plain) > $start + 180 ? '...' : '');
    }

    protected function indexOf($haystack, $needle)
    {
        if (function_exists('mb_strpos')) {
            return mb_strpos((string)$haystack, (string)$needle, 0, 'UTF-8');
        }

        return strpos((string)$haystack, (string)$needle);
    }

    protected function plainText($content)
    {
        $content = preg_replace('/^---\R.*?\R---\R/s', '', ltrim((string)$content), 1);
        $content = preg_replace('/```.*?```/s', ' ', $content);
        $content = preg_replace('/`([^`]*)`/', '$1', $content);
        $content = preg_replace('/!\[([^\]]*)\]\([^)]+\)/', '$1', $content);
        $content = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $content);
        $content = preg_replace('/[#>*_\-|]+/', ' ', $content);

        return preg_replace('/\s+/u', ' ', $content);
    }

    protected function normalizeQuery($query)
    {
        $query = preg_replace('/\s+/u', ' ', trim((string)$query));
        if ($query === '' || $this->length($query) < 2) {
            return '';
        }

        return $this->lower($query);
    }

    protected function lower($value)
    {
        $value = (string)$value;
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }

    protected function length($value)
    {
        return function_exists('mb_strlen') ? mb_strlen((string)$value, 'UTF-8') : strlen((string)$value);
    }

    protected function cut($value, $start, $length)
    {
        if (function_exists('mb_substr')) {
            return mb_substr($value, $start, $length, 'UTF-8');
        }

        return substr($value, $start, $length);
    }

    protected function compareResults($left, $right)
    {
        if ($left['score'] === $right['score']) {
            return strcasecmp($left['title'], $right['title']);
        }

        return $left['score'] > $right['score'] ? -1 : 1;
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
