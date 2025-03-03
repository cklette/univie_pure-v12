<?php
namespace Univie\UniviePure\Endpoints;

use Univie\UniviePure\Service\WebService;
use Univie\UniviePure\Utility\CommonUtilities;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * (c) 2021 Christian Klettner <christian.klettner@univie.ac.at>, univie
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class returns basic information about a person as entered in pure and the portal url for a person
 */
class Persons
{

    /**
     * @param string $uuid
     * @return string
     */
    public function getProfile($uuid)
    {
        $xml = '<?xml version="1.0"?>
                <personsQuery>
                <uuids><uuid>' . $uuid . '</uuid></uuids>
                <rendering>short</rendering>
                <linkingStrategy>portalLinkingStrategy</linkingStrategy>';

        //set locale:
        $xml .= CommonUtilities::getLocale();

        $xml .= '</personsQuery>';

        $webservice = new WebService;
        $profile = $webservice->getJson('persons', $xml);

        return $profile['items'][0]['rendering'][0]['value'];
    }

    /**
     * @param string $uuid
     * @return string
     */
    public function getPortalUrl($uuid)
    {
        $xml = '<?xml version="1.0"?>
                <personsQuery>
                <uuids><uuid>' . $uuid . '</uuid></uuids>
                <linkingStrategy>portalLinkingStrategy</linkingStrategy>';
        //set locale:
        $xml .= CommonUtilities::getLocale();
        $xml .= '<fields><fields>info.portalUrl</fields></fields>
            </personsQuery>';
        $webservice = new WebService;
        $portalUrl = $webservice->getJson('persons', $xml);

        return $portalUrl['items'][0]['info']['portalUrl'];
    }
}
