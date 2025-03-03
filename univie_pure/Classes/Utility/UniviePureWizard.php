<?php

namespace Univie\UniviePure\Utility;

class UniviePureWizard
{

    /*
    * Processing the wizard items array
    *
    * @param array $wizardItems The wizard items
    * @return array Modified array with wizard items
    */
    function proc($wizardItems)
    {
        $wizardItems['plugins_tx_univiepure'] = [
            'icon' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('univie_pure') . 'Resources/Public/Icons/ucris-icon.svgf',
            'title' => $GLOBALS['LANG']->sL('LLL:EXT:univie_pure/Resources/Private/Language/locallang.xlf:univiepur.title'),
            'description' => $GLOBALS['LANG']->sL('LLL:EXT:univie_pure/Resources/Private/Language/locallang.xlf:univiepur.description'),
            'params' => '&defVals[tt_content][CType]=list&&defVals[tt_content][list_type]=univiepure_pi1'
        ];
        return $wizardItems;
    }
}
