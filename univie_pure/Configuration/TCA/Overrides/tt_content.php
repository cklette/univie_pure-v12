<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();
$extensionKey = 'univie_pure';
$pluginName = 'Pi1';
$pluginTitle = 'Pure';
$pluginIcon ='univie-pure-pi1';

$pluginSignature = ExtensionUtility::registerPlugin(
    $extensionKey,
    $pluginName,
    $pluginTitle
);

//$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'list,detail';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature]
    = 'pi_flexform';

ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:univie_pure/Configuration/FlexForms/Flexform.xml'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'recursive,pages';
