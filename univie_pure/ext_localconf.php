<?php

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'UniviePure',
    'Pi1',
    [\Univie\UniviePure\Controller\PureController::class => 'list,show'],
    [],
);

$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] = array_merge(
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'],
    [
        'currentPage', // e.g. if "plugin.tx_kesearch_pi1.searchWordParameter = q"
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1485351217] = [
    'nodeName' => 'getUcrisPerson',
    'priority' => 70,
    'class' => \Univie\UniviePure\Utility\PureWizard::class
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1740545779] = [
    'nodeName' => 'getUcrisProject',
    'priority' => 70,
    'class' => \Univie\UniviePure\Utility\PureProjectWizard::class
];