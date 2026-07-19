<?php
/**
 * mxLocDoc namespace bootstrap.
 *
 * Loaded automatically by the MODX 3 core for the mxlocdoc namespace
 * (see \MODX\Revolution\modX::_loadExtensionPackages).
 *
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 *
 * @package mxlocdoc
 */

require_once $namespace['path'] . 'vendor/autoload.php';

$modx->services->add('mxlocdoc', function ($c) use ($modx) {
    return new \MxLocDoc\MxLocDoc($modx);
});
