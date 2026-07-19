<?php
namespace MxLocDoc\Services;

use MODX\Revolution\modX;
use MxLocDoc\MxLocDoc;

/**
 * Markdown document repository.
 *
 * @package mxlocdoc
 */
class DocumentRepository
{
    /** @var modX */
    protected $modx;

    /** @var PathResolver */
    protected $pathResolver;

    protected $allowedExtensions = array('md', 'markdown');

    public function __construct(modX &$modx, PathResolver $pathResolver)
    {
        $this->modx =& $modx;
        $this->pathResolver = $pathResolver;
    }

    public function get($path)
    {
        $resolved = $this->pathResolver->resolveFile($path);
        if (!$resolved['success']) {
            return $resolved;
        }

        $extension = $this->pathResolver->normalizeExtension($resolved['path']);
        if (!in_array($extension, $this->allowedExtensions, true)) {
            return $this->failure('extension_not_allowed', $this->modx->lexicon('mxlocdoc_error_document_extension'));
        }

        $sizeCheck = $this->pathResolver->checkFileSize($resolved['path']);
        if (!$sizeCheck['success']) {
            return $sizeCheck;
        }

        $content = file_get_contents($resolved['path']);
        if ($content === false) {
            return $this->failure('file_not_readable', $this->modx->lexicon('mxlocdoc_error_file_not_readable'));
        }

        return array(
            'success' => true,
            'path' => $resolved['relative_path'],
            'name' => basename($resolved['path']),
            'extension' => $extension,
            'size' => filesize($resolved['path']),
            'content' => $content,
        );
    }

    public function exists($path)
    {
        $resolved = $this->pathResolver->resolveFile($path);
        if (!$resolved['success']) {
            return false;
        }

        $extension = $this->pathResolver->normalizeExtension($resolved['path']);
        return in_array($extension, $this->allowedExtensions, true);
    }

    public function getMetadata($path)
    {
        $document = $this->get($path);
        if (!$document['success']) {
            return $document;
        }

        $frontMatter = $this->parseFrontMatter($document['content']);

        return array(
            'success' => true,
            'path' => $document['path'],
            'name' => $document['name'],
            'extension' => $document['extension'],
            'size' => $document['size'],
            'title' => isset($frontMatter['title']) ? $frontMatter['title'] : $this->titleFromPath($document['path']),
            'order' => isset($frontMatter['order']) ? (int)$frontMatter['order'] : 0,
            'hidden' => !empty($frontMatter['hidden']),
        );
    }

    public function listAll()
    {
        $root = $this->pathResolver->resolveDirectory('');
        if (!$root['success']) {
            return $root;
        }

        $paths = array();
        $this->collectMarkdownFiles($root['path'], $root['path'], $paths);
        sort($paths, SORT_NATURAL | SORT_FLAG_CASE);

        $items = array();
        foreach ($paths as $path) {
            $metadata = $this->getMetadata($path);
            if (!empty($metadata['success'])) {
                $items[] = $metadata;
            }
        }

        return array(
            'success' => true,
            'items' => $items,
        );
    }

    protected function collectMarkdownFiles($directory, $root, array &$paths)
    {
        $entries = scandir($directory);
        if ($entries === false) {
            return;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($path)) {
                $this->collectMarkdownFiles($path, $root, $paths);
                continue;
            }

            if (!is_file($path)) {
                continue;
            }

            $extension = $this->pathResolver->normalizeExtension($path);
            if (!in_array($extension, $this->allowedExtensions, true)) {
                continue;
            }

            $paths[] = str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($root)));
        }
    }

    protected function parseFrontMatter($content)
    {
        $content = ltrim((string)$content);
        if (strpos($content, "---\n") !== 0 && strpos($content, "---\r\n") !== 0) {
            return array();
        }

        if (!preg_match('/^---\\R(.*?)\\R---\\R/s', $content, $matches)) {
            return array();
        }

        $data = array();
        foreach (preg_split('/\\R/', trim($matches[1])) as $line) {
            if (strpos($line, ':') === false) {
                continue;
            }

            list($key, $value) = explode(':', $line, 2);
            $key = strtolower(trim($key));
            $value = trim($value, " \t\n\r\0\x0B\"'");

            if ($key === 'title') {
                $data['title'] = $value;
            } elseif ($key === 'order') {
                $data['order'] = is_numeric($value) ? (int)$value : 0;
            } elseif ($key === 'hidden') {
                $data['hidden'] = in_array(strtolower($value), array('1', 'true', 'yes', 'on'), true);
            }
        }

        return $data;
    }

    protected function titleFromPath($path)
    {
        $name = basename($path);
        $base = preg_replace('/\\.(md|markdown)$/i', '', $name);
        if (strtolower($base) === 'readme') {
            $directory = trim(dirname($path), '.');
            $base = $directory === '' ? 'README' : basename($directory);
        }

        $base = str_replace(array('-', '_'), ' ', $base);
        return ucwords($base);
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
