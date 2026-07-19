<?php
namespace MxLocDoc\Services;

use MODX\Revolution\modX;
use MxLocDoc\MxLocDoc;

/**
 * Safe filesystem path resolver for mxLocDoc.
 *
 * @package mxlocdoc
 */
class PathResolver
{
    /** @var modX */
    protected $modx;

    /** @var MxLocDoc */
    protected $mxlocdoc;

    /** @var string|null */
    protected $rootPath = null;

    /** @var string|null */
    protected $basePath = null;

    /** @var array|null */
    protected $languages = null;

    public function __construct(modX &$modx, MxLocDoc $mxlocdoc)
    {
        $this->modx =& $modx;
        $this->mxlocdoc = $mxlocdoc;
    }

    public function getRootPath()
    {
        if ($this->rootPath !== null) {
            return $this->success($this->rootPath);
        }

        $base = $this->getBasePath();
        if (!$base['success']) {
            return $base;
        }

        $root = $base['path'];
        $languages = $this->getAvailableLanguages();
        if (!empty($languages)) {
            $language = $this->selectLanguage($languages);
            $root = realpath($base['path'] . $language . DIRECTORY_SEPARATOR);
            if ($root === false || !is_dir($root)) {
                return $this->failure('docs_path_invalid', $this->modx->lexicon('mxlocdoc_error_docs_path_invalid'));
            }
            $this->mxlocdoc->config['language'] = $language;
        }

        $this->rootPath = rtrim($this->normalizeDirectorySeparators($root), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return $this->success($this->rootPath);
    }

    public function getLanguageContext()
    {
        $root = $this->getRootPath();
        if (!$root['success']) {
            return $root;
        }

        $languages = $this->getAvailableLanguages();
        return array(
            'success' => true,
            'language' => empty($languages) ? '' : $this->mxlocdoc->config['language'],
            'languages' => array_values($languages),
            'is_multilingual' => count($languages) > 1,
            'root_path' => $root['path'],
        );
    }

    public function getAvailableLanguages()
    {
        if ($this->languages !== null) {
            return $this->languages;
        }

        $configured = $this->getConfiguredLanguages();
        if (empty($configured)) {
            $this->languages = array();
            return $this->languages;
        }

        $base = $this->getBasePath();
        if (!$base['success']) {
            $this->languages = array();
            return $this->languages;
        }

        $languages = array();
        foreach ($configured as $code) {
            $path = $base['path'] . $code . DIRECTORY_SEPARATOR;
            if (is_dir($path) && $this->hasDocumentationFiles($path)) {
                $languages[$code] = array(
                    'code' => $code,
                    'label' => strtoupper($code),
                );
            }
        }

        ksort($languages);
        $this->languages = $languages;
        return $this->languages;
    }

    protected function getConfiguredLanguages()
    {
        $result = array();
        $configured = isset($this->mxlocdoc->config['languages']) ? $this->mxlocdoc->config['languages'] : array();
        foreach ((array)$configured as $code) {
            $code = $this->mxlocdoc->normalizeLanguage($code);
            if ($code !== '' && !in_array($code, $result, true)) {
                $result[] = $code;
            }
        }

        return $result;
    }

    public function resolveFile($path)
    {
        $root = $this->getRootPath();
        if (!$root['success']) {
            return $root;
        }

        $relativePath = $this->normalizeRelativePath($path);
        if ($relativePath === '') {
            $relativePath = (string)$this->mxlocdoc->config['default_file'];
        }

        if ($relativePath === '' || $this->hasUnsafePathSegments($relativePath)) {
            return $this->failure('path_invalid', $this->modx->lexicon('mxlocdoc_error_path_invalid'));
        }

        $fullPath = realpath($root['path'] . $relativePath);
        if ($fullPath === false || !is_file($fullPath)) {
            return $this->failure('file_not_found', $this->modx->lexicon('mxlocdoc_error_file_not_found'));
        }

        $fullPath = $this->normalizeDirectorySeparators($fullPath);
        if (!$this->isInsideRoot($fullPath, $root['path'])) {
            return $this->failure('path_outside_root', $this->modx->lexicon('mxlocdoc_error_path_outside_root'));
        }

        return $this->success($fullPath, $this->getRelativePath($fullPath, $root['path']));
    }

    public function resolveDirectory($path = '')
    {
        $root = $this->getRootPath();
        if (!$root['success']) {
            return $root;
        }

        $relativePath = $this->normalizeRelativePath($path);
        if ($relativePath !== '' && $this->hasUnsafePathSegments($relativePath)) {
            return $this->failure('path_invalid', $this->modx->lexicon('mxlocdoc_error_path_invalid'));
        }

        $fullPath = realpath($root['path'] . $relativePath);
        if ($fullPath === false || !is_dir($fullPath)) {
            return $this->failure('directory_not_found', $this->modx->lexicon('mxlocdoc_error_directory_not_found'));
        }

        $fullPath = rtrim($this->normalizeDirectorySeparators($fullPath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!$this->isInsideRoot($fullPath, $root['path']) && $fullPath !== $root['path']) {
            return $this->failure('path_outside_root', $this->modx->lexicon('mxlocdoc_error_path_outside_root'));
        }

        return $this->success($fullPath, $this->getRelativePath($fullPath, $root['path']));
    }

    public function normalizeDocumentPath($path)
    {
        return $this->normalizeRelativePath($path);
    }

    public function normalizeExtension($path)
    {
        return strtolower((string)pathinfo((string)$path, PATHINFO_EXTENSION));
    }

    public function checkFileSize($path)
    {
        $maxSize = (int)$this->mxlocdoc->config['max_file_size'];
        if ($maxSize <= 0) {
            return $this->success($path);
        }

        $size = filesize($path);
        if ($size === false) {
            return $this->failure('file_not_readable', $this->modx->lexicon('mxlocdoc_error_file_not_readable'));
        }

        if ($size > $maxSize) {
            return $this->failure('file_too_large', $this->modx->lexicon('mxlocdoc_error_file_too_large'));
        }

        return $this->success($path);
    }

    protected function getBasePath()
    {
        if ($this->basePath !== null) {
            return $this->success($this->basePath);
        }

        $path = $this->resolveRootSetting((string)$this->mxlocdoc->config['docs_path']);
        if ($path === '') {
            return $this->failure('docs_path_empty', $this->modx->lexicon('mxlocdoc_error_docs_path_empty'));
        }

        $base = realpath($path);
        if ($base === false || !is_dir($base)) {
            return $this->failure('docs_path_invalid', $this->modx->lexicon('mxlocdoc_error_docs_path_invalid'));
        }

        $this->basePath = rtrim($this->normalizeDirectorySeparators($base), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return $this->success($this->basePath);
    }

    protected function selectLanguage(array $languages)
    {
        $requested = $this->mxlocdoc->normalizeLanguage($this->mxlocdoc->config['language']);
        if ($requested !== '' && isset($languages[$requested])) {
            return $requested;
        }

        $managerLanguage = $this->mxlocdoc->normalizeLanguage($this->modx->getOption('manager_language', null, ''));
        if ($managerLanguage !== '' && isset($languages[$managerLanguage])) {
            return $managerLanguage;
        }

        $cultureKey = $this->mxlocdoc->normalizeLanguage($this->modx->getOption('cultureKey', null, ''));
        if ($cultureKey !== '' && isset($languages[$cultureKey])) {
            return $cultureKey;
        }

        $codes = array_keys($languages);
        return reset($codes);
    }

    protected function hasDocumentationFiles($directory)
    {
        $defaultFile = (string)$this->mxlocdoc->config['default_file'];
        $navFile = (string)$this->mxlocdoc->config['nav_file'];
        foreach (array($defaultFile, $navFile, 'mxlocdoc.json') as $file) {
            if ($file !== '' && is_file($directory . $file)) {
                return true;
            }
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && preg_match('/\.(md|markdown)$/i', $file->getFilename())) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeRelativePath($path)
    {
        $path = str_replace('\\', '/', trim((string)$path));
        $path = preg_replace('#/+#', '/', $path);
        return ltrim($path, '/');
    }

    protected function resolveRootSetting($path)
    {
        $path = trim((string)$path);
        if ($path === '') {
            return '';
        }

        $path = str_replace(
            array('[[+corePath]]', '[[+basePath]]', '[[+assetsPath]]'),
            array(
                $this->modx->getOption('core_path'),
                $this->modx->getOption('base_path'),
                $this->modx->getOption('assets_path'),
            ),
            $path
        );

        if (!$this->isAbsolutePath($path)) {
            $relativePath = trim($this->normalizeRelativePath($path), '/');
            if ($relativePath === '' || $this->hasUnsafePathSegments($relativePath)) {
                return '';
            }
            $path = rtrim($this->modx->getOption('core_path'), '/\\') . DIRECTORY_SEPARATOR . $relativePath;
        }

        return $this->normalizeDirectorySeparators($path);
    }

    protected function isAbsolutePath($path)
    {
        $path = (string)$path;
        return strpos($path, '/') === 0 || preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1;
    }

    protected function normalizeDirectorySeparators($path)
    {
        return str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    }

    protected function hasUnsafePathSegments($path)
    {
        $segments = explode('/', str_replace('\\', '/', $path));
        foreach ($segments as $segment) {
            if ($segment === '..' || $segment === '') {
                return true;
            }
        }
        return false;
    }

    protected function isInsideRoot($path, $root)
    {
        $root = rtrim($this->normalizeDirectorySeparators($root), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return strpos($path, $root) === 0;
    }

    protected function getRelativePath($path, $root)
    {
        $root = rtrim($this->normalizeDirectorySeparators($root), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($root)));
    }

    protected function success($path, $relativePath = '')
    {
        return array(
            'success' => true,
            'path' => $path,
            'relative_path' => $relativePath,
        );
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
