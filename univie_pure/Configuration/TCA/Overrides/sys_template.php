<?php

defined('TYPO3') || die();


call_user_func(function () {

    /**

     * Extension key

     */

    $extensionKey = 'univie_pure';


    /**

     * Add default TypoScript (constants and setup)

     */

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(

        $extensionKey,

        'Configuration/TypoScript',

        'Pure u:cris'

    );
});
