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
 * returns Press/Media entries for a person, institution or project
 */
class PressMedia
{

    /**
     * produce xml for the list query of press-media
     * @params array $settings
     * @params string $cObjUid
     * @return array $pressMedia
     */
    public function getPressMediaList($settings, $cObjUid)
    {
        $xml = '<?xml version="1.0"?>
            <pressMediaQuery>';

        // If a list of UUIDs was entered:
        if ($settings['chooseSelector'] == 3) {
            $xml .= CommonUtilities::getUuidListXml($settings);
        }

        // page size:
        $xml .= CommonUtilities::getPageSize($settings['pageSize']);

        // offset:
        // with params pagesize, uid of content, pagername
        $xml .= CommonUtilities::getOffset($settings['pageSize'], $settings['currentPage']);

        // linkinStrategy:
        $xml .= '<linkingStrategy>portalLinkingStrategy</linkingStrategy>';

        // locale:
        $xml .= CommonUtilities::getLocale();

        // renderings:
        /* $xml .= '<renderings>
                <rendering>short</rendering>
            </renderings>'; */

        // ordering:
        $xml .= '<orderings><ordering>-date</ordering></orderings>';

        // search AND filter:
        if ($settings['narrowBySearch'] || $settings['filter']) {
            $xml .= $this->getSearchXml($settings);
        }

        // either for organisations or for persons, both must not be submitted:
        $xml .= CommonUtilities::getPersonsOrOrganisationsXml($settings);
        $xml .= '</pressMediaQuery>';

        $webservice = new WebService;
        $pressMedia = $webservice->getJson('press-media', $xml);
        return $pressMedia;
    }

    /**
     * xml for search string
     * @param array $settings
     * @return string $xml
     */
    public function getSearchXml($settings)
    {
        $terms = $settings['narrowBySearch'];
        // combine the backend filter and the frontend form:
        if ($settings['filter']) {
            $terms .= ' ' . $settings['filter'];
        }
        $xml .= '<searchString>' . trim($terms) . '</searchString>';
        return $xml;
    }
}
