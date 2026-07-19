<?php
/**
 * Clear mxLocDoc cache when MODX manager cache is cleared.
 *
 * @var \MODX\Revolution\modX $modx
 *
 * @package mxlocdoc
 */
if (!$modx instanceof \MODX\Revolution\modX || !$modx->event || $modx->event->name !== 'OnBeforeCacheUpdate') {
    return;
}

$mxlocdoc = $modx->services->has('mxlocdoc') ? $modx->services->get('mxlocdoc') : null;

if ($mxlocdoc) {
    $mxlocdoc->clearCache();
}
