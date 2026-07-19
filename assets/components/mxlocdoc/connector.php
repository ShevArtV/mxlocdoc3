<?php
/**
 * mxLocDoc manager connector.
 *
 * @package mxlocdoc
 */
require_once dirname(__FILE__, 4) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

/** @var \MODX\Revolution\modX $modx */
if (!$modx->services->has('mxlocdoc')) {
    http_response_code(503);
    echo $modx->toJSON(array('success' => false, 'message' => 'mxLocDoc service unavailable'));
    exit;
}

/** @var \MxLocDoc\MxLocDoc $mxlocdoc */
$mxlocdoc = $modx->services->get('mxlocdoc');
$modx->lexicon->load('mxlocdoc:default');

$modx->request->handleRequest(array(
    'processors_path' => $mxlocdoc->config['core_path'] . 'processors/',
    'location' => '',
));
