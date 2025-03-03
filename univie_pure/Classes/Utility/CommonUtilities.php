<?php

namespace Univie\UniviePure\Utility;

use Univie\UniviePure\Service\WebService;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Context\Context;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Christian Klettner <christian.klettner@univie.ac.at>, univie
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Helpers for all endpoints
 *
 */
class CommonUtilities
{

    protected $locale;

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * xml for frontend locale
     * @ return String xml
     */
    public static function getLocale($locale)
    {
        $lang = ($locale == 'de') ? 'de_DE' : 'en_GB';
        $xml = '<locales><locale>' . $lang . '</locale></locales>';
        return $xml;
    }

    /**
     * get backend locale
     * @ return String locale
     */
    public static function getBackendLanguage()
    {
        return $bl = ($GLOBALS['BE_USER']->user['lang'] == 'de') ? 'de_DE' : 'en_EN';
    }

    /**
     * page size entered in flexform
     * @return String xml
     */
    public static function getPageSize($pageSize)
    {
        if ($pageSize == 0 || $pageSize === NULL) $pageSize = 20;
        $xml = '<size>' . $pageSize . '</size>';
        return $xml;
    }

    /**
     * keep track of the counter
     * Do this per endpoint plus per content element
     * @param int $pageSize
     * @param int $cObjUid
     * @param string $pagerName
     * @return String xml
     */
    public static function getOffsetOld($pageSize, $cObjUid)
    {
        // pager is in form pagerName[cObjUid]=1, is returned in form pagerName[123456][1]
        //$p = $pagerName . '[' . $cObjUid . ']';
        //$pager = $_GET('pager');
        // Check if offset is meant for this plug-in via uid in url and uid in controller settings:
        $cobj = $_GET['cobjuid'];
        // Check for pager per endpoint and content element uid for multiple pagers on one site:
        //if (is_array($pager)) {
        if ($cobj == $cObjUid) {
            //$offset = array_key_exists($cObjUid, $pager) ? $pager[$cObjUid] : 0;
            //$offset = $_GET['currentpage'];
            $offset = ($offset - 1 < 0) ? 0 : $offset - 1;
        } else {
            $offset = 0;
        }
        $xml = '<offset>' . $offset * $pageSize . '</offset>';
        return $xml;
    }
    public static function getOffset($pageSize, $currentPage)
    {
        $offset = ($currentPage - 1 < 0) ? 0 : $currentPage - 1;
        $xml = '<offset>' . $offset * $pageSize . '</offset>';
        return $xml;
    }

    /**
     * Either send a request for a unit or for persons
     * @return String xml
     */
    public static function getPersonsOrOrganisationsXml($settings)
    {
        $xml = '';
        // either for organisations or for persons or for projects:
        // If settings.chooseSelector equals 0 => organisational units, case 1 => persons, case 2 => projects:
        switch ($settings['chooseSelector']) {
            case 0:
                // Resarch-output for organisations:
                $xml = self::getOrganisationsXml($settings);
                break;
            case 1:
                // Research-output for persons:
                $xml = self::getPersonsXml($settings);
                break;
            case 2:
                // Research-output for projects:
                // This is handled directly as the produced list of uuids has to be on first place
                break;
            case 3:
                // Research-output by means of raw pure UUID list:
                //This is handled directly as the produced list of uuids has to be on first place
                break;
        }
        return $xml;
    }

    /**
     * Organisations query
     * @return String xml
     */
    public static function getOrganisationsXml($settings)
    {
        //if search is entered organisations may be omitted:
        if ($settings['selectorOrganisations'] == '' && $settings['narrowBySearch'] != '') return $xml;
        //otherwise allways write the xml. If organisations are empty nothing is returned from ucris:
        $xml = '<forOrganisationalUnits><uuids>';
        $organisations = explode(',', $settings['selectorOrganisations']);
        foreach ((array) $organisations as $org) {
            if (strpos($org, "|")) {
                $tmp = explode("|", $org);
                $org = $tmp[0];
            }
            $xml .= '<uuid>' . $org . '</uuid>';
            //check for sub units:
            if ($settings['includeSubUnits'] == 1) {
                $subUnits = self::getSubUnits($org);
                if (is_array($subUnits) && count($subUnits) > 1) {
                    foreach ($subUnits as $subUnit) {
                        if ($subUnit['uuid'] != $org) {
                            $xml .= '<uuid>' . $subUnit['uuid'] . '</uuid>';
                        }
                    }
                }
            }
        }
        $xml .= '</uuids></forOrganisationalUnits>';
        return $xml;
    }

    /**
     * Persons query
     * @return String xml
     */
    public static function getPersonsXml($settings)
    {
        //if search is entered persons may be omitted:
        if ($settings['selectorPersons'] == '' && $settings['narrowBySearch'] != '') return $xml;
        //otherwise allways write the xml. If persons are empty nothing is returned from ucris:
        $xml = '<forPersons><uuids>';
        $persons = explode(',', $settings['selectorPersons']);
        foreach ((array) $persons as $person) {
            if (strpos($person, "|")) {
                $tmp = explode("|", $person);
                $person = $tmp[0];
            }
            $xml .= '<uuid>' . $person . '</uuid>';
        }
        $xml .= '</uuids></forPersons>';
        return $xml;
    }

    /**
     * UUID list
     * @return String xml
     */
    public static function getUuidListXml($settings)
    {
        $xml = '<uuids>';
        $uuids = explode(PHP_EOL, $settings['selectorUuids']);
        foreach ((array) $uuids as $uuid) {
            $xml .= '<uuid>' . $uuid . '</uuid>';
        }
        $xml .= '</uuids>';
        return $xml;
    }

    /**
     * Projects query
     * @return String xml
     */
    public static function getProjectsXml($settings)
    {
        $xml = '';
        // Prevent showing ALL publications if project id is empty:
        if (!$settings['selectorProjects'] && !$settings['narrowBySearch']) return '<uuids></uuids>';
        $xmlProjects = '<?xml version="1.0"?>
            <projectsQuery>
                <uuids>';
        $projects = explode(',', $settings['selectorProjects']);
        foreach ((array) $projects as $project) {
            if (strpos($project, "|")) {
                $tmp = explode("|", $project);
                $project = $tmp[0];
            }
            $xmlProjects .= '<uuid>' . $project . '</uuid>';
        }
        $xmlProjects .= '</uuids>
                <size>20000</size>
                <locales><locale>de_DE</locale></locales>
                <fields><field>relatedResearchOutputs.uuid</field></fields>
                <orderings><ordering>title</ordering></orderings>
            </projectsQuery>';
        $webservice = new WebService;
        $publications = $webservice->getJson('projects', $xmlProjects);

        if ($publications['count'] > 0) {
            $xml .= '<uuids><uuid></uuid>';
            foreach ((array) $publications['items'][0]['relatedResearchOutputs'] as $researchOutput) {
                $xml .= '<uuid>' . $researchOutput['uuid'] . '</uuid>';
            }
            $xml .= '</uuids>';
        }
        return $xml;
    }

    /**
     * query sub organisations for a unit
     * @return array subUnits Array of all Units connected
     */
    public static function getSubUnits($orgId)
    {
        $xml = '<?xml version="1.0"?>
            <organisationalUnitsQuery>
                <uuids><uuid>' . $orgId . '</uuid></uuids>
                <size>1000</size>
                <fields><field>*</field></fields>
                <orderings><ordering>type</ordering></orderings>
                <returnUsedContent>true</returnUsedContent>
                <navigationLink>true</navigationLink>
                <organisationalUnitPeriodStatus>ACTIVE</organisationalUnitPeriodStatus>
                <hierarchyDepth>3</hierarchyDepth>
            </organisationalUnitsQuery>';
        $webservice = new WebService;
        $subUnits = $webservice->getJson('organisational-units', $xml);
        if ($subUnits['count'] > 1) {
            return $subUnits['items'];
        }
    }

    /*
     * query name by uuid
     * @return string name
     */
    public static function getNameForUuid($orgId)
    {
        $xml = '<?xml version="1.0"?>
                <organisationalUnitsQuery>
                    <uuids><uuid>' . $orgId . '</uuid></uuids>
                    <locales><locale>de_DE</locale></locales>
                    <fields><field>name.*</field></fields>
                    <organisationalUnitPeriodStatus>ACTIVE</organisationalUnitPeriodStatus>
                </organisationalUnitsQuery>';
        $webservice = new WebService;
        $orgName = $webservice->getJson('organisational-units', $xml);
        if ($orgName['count'] == 1) {
            return $orgName['items'][0]['name']['text'][0]['value'];
        }
    }
}
