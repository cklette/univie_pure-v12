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

class Activities
{
    /**
     * produce xml for the list query of activities
     * @return array $activites
     */
    public function getActivitiesList($settings, $cObjUid)
    {

        $xml = '<?xml version="1.0"?>
            <activitiesQuery>';

        // If a list of UUIDs was entered:
        if ($settings['chooseSelector'] == 3) {
            $xml .= CommonUtilities::getUuidListXml($settings);
        }

        //set page size:
        $xml .= CommonUtilities::getPageSize($settings['pageSize']);

        //set offset:
        $xml .= CommonUtilities::getOffset($settings['pageSize'], $settings['currentPage']);

        //linking:
        $xml .= '<linkingStrategy>portalLinkingStrategy</linkingStrategy>';

        //set locale:
        $xml .= CommonUtilities::getLocale();

        // pre-defined rendering for univie_personal only:
        if ($settings['renderingPers'] != '') {
            $xml .= '<renderings><rendering>' . $settings['renderingPers'] . '</rendering></renderings>';
        }

        //ordering:
        $xml .= '<orderings><ordering>-startDate</ordering></orderings>';

        //classification scheme types:
        if (($settings['narrowByActivitiesType'] == 1) && ($settings['selectorActivitiesType'] != '')) {
            $xml .= $this->getActivityTypesXml($settings['selectorActivitiesType']);
        }

        //either for organisations or for persons, both must not be submitted:
        $xml .= CommonUtilities::getPersonsOrOrganisationsXml($settings);
        $xml .= '</activitiesQuery>';

        $webservice = new WebService;
        $activities = $webservice->getJson('activities', $xml);

        return $activities;
    }

    /**
     * query for classificationscheme
     * @return string xml
     */
    public function getActivityTypesXml($activityTypes)
    {
        $xml = '<typeUris>';
        $types = explode(',', $activityTypes);
        foreach ((array) $types as $type) {
            if (strpos($type, "|")) {
                $tmp = explode("|", $type);
                $type = $tmp[0];
            }
            $xml .= '<typeUri>' . $type . '</typeUri>';
        }
        $xml .= '</typeUris>';
        return $xml;
    }
}
