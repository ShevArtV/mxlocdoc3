<?php
namespace MxLocDoc\Services;

use MODX\Revolution\modX;
use MxLocDoc\MxLocDoc;

/**
 * Protected asset repository.
 *
 * @package mxlocdoc
 */
class AssetRepository
{
    /** @var modX */
    protected $modx;

    /** @var MxLocDoc */
    protected $mxlocdoc;

    /** @var PathResolver */
    protected $pathResolver;

    protected $mimeTypes = array(
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'pdf' => 'application/pdf',
    );

    public function __construct(modX &$modx, MxLocDoc $mxlocdoc, PathResolver $pathResolver)
    {
        $this->modx =& $modx;
        $this->mxlocdoc = $mxlocdoc;
        $this->pathResolver = $pathResolver;
    }

    public function get($path)
    {
        $resolved = $this->pathResolver->resolveFile($path);
        if (!$resolved['success']) {
            return $resolved;
        }

        $extension = $this->pathResolver->normalizeExtension($resolved['path']);
        if (!$this->isAllowedExtension($extension)) {
            return $this->failure('extension_not_allowed', $this->modx->lexicon('mxlocdoc_error_asset_extension'));
        }

        $sizeCheck = $this->pathResolver->checkFileSize($resolved['path']);
        if (!$sizeCheck['success']) {
            return $sizeCheck;
        }

        return array(
            'success' => true,
            'path' => $resolved['path'],
            'relative_path' => $resolved['relative_path'],
            'name' => basename($resolved['path']),
            'extension' => $extension,
            'mime' => $this->getMimeType($extension),
            'size' => filesize($resolved['path']),
        );
    }

    protected function isAllowedExtension($extension)
    {
        return in_array($extension, $this->mxlocdoc->config['allowed_asset_extensions'], true);
    }

    protected function getMimeType($extension)
    {
        return isset($this->mimeTypes[$extension]) ? $this->mimeTypes[$extension] : 'application/octet-stream';
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
