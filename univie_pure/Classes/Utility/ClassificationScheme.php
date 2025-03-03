<?php
namespace Univie\UniviePure\Utility;

use Univie\UniviePure\Utility\CommonUtilities;
use Univie\UniviePure\Service\WebService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2021 Christian Klettner <christian.klettner@univie.ac.at>, univie
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
 * ClassificationScheme and structural queries
 *
 *
 * /ws/rest/classificationschemehierarchy?baseUri=/dk/atira/pure/organisation/organisationtypes
 * /ws/rest/classificationschemehierarchy?baseUri=/dk/atira/pure/researchoutput/researchoutputtypes
 * /ws/rest/classificationschemehierarchy?baseUri=/dk/atira/pure/activity/activitytypes
 * /ws/rest/classificationschemehierarchy?baseUri=/dk/atira/pure/person/employmenttypes
 *
 */
class ClassificationScheme
{

    const RESEARCHOUTPUT = '/dk/atira/pure/researchoutput/researchoutputtypes';

    const ACTIVITIES = '/dk/atira/pure/activity/activitytypes';

    const PRESSMEDIA = '/dk/atira/pure/clipping/clippingtypes';

    const PROJECTS = '/dk/atira/pure/upm/fundingprogramme';

    /**
     * @var $lang String
     */
    protected $locale = '';

    /**
     * set common stuff
     * @return void
     */
    public function __construct()
    {
        // Set the backend language:
        $this->locale = CommonUtilities::getBackendLanguage();
    }

    /**
     * getter for locale
     * @return string $locale frontend language
     */
    private function getLocale()
    {
        return $this->locale;
    }

    /*
     * Ajax call for backend choosing persons:
     * @param object \TYPO3\CMS\Backend\Form\FormEngine $tceForms Reference to an TCEforms instance
     * @return string $output
     */
    public function ajaxGetPerson($PA, $fObj)
    {

        $query = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('query');

        if ($query) {
            $items = [];
            $xml ='<personsQuery>
                        <size>100</size>
                        <locales>
                            <locale>' . $this->getLocale() . '</locale>
                        </locales>
                        <fields>
                            <field>name.*</field>
                            <field>uuid</field>
                        </fields>
                        <searchString>' . $query . '</searchString>
                    </personsQuery>';
            $webservice = new WebService;
            $persons = $webservice->getJson('persons',$xml);
            foreach ($persons['items'] as $pers) {
                $p = $pers['name']['lastName'] . ', ' . $pers['name']['firstName'];
                $item = [
                    '0' => $pers['name']['lastName'] . ', ' . $pers['name']['firstName'],
                    '1' => $pers['uuid'],
                ];
                array_push($items,$item);
            }
        }

        // Put the wizard into $output and return it
        $output = '<style>.typo3-TCEforms-suggest-resultlist li:hover{background-color:#ffb;}</style>
            <div style="margin-top: 8px; margin-left: 4px;">
            <input type="text" name="query" id="query" placeholder="'
                . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('suggest-placeholder','univie_pure') .
            '" autocomplete="off">';

        if (isset($items)) {
            $output .= '<div class="typo3-TCEforms-suggest-choices" id="typo3-ucris-persons-suggest">
            <ul class="typo3-TCEforms-suggest-resultlist">';
            foreach ($items as $item) {
                $output .= '<li onclick="addToList(this.id)" id="' . $item[1] . '">
                        <span class="suggest-label"><span title="' . $item[0] . '">' . $item[0] . '</span></span><br><span class="suggest-uid">' . $item[1] . '</span>
                    </li>';
            }
            $output .= '</ul></div>';
        }

        $output .= '</div>
            <script>
            function addToList(id){
                var x = document.getElementById(id);
                var nodeList = x.childNodes;
                var opt = document.createElement("option");
                opt.value = id;
                opt.text = nodeList[1].innerText;
                opt.setAttribute("selected", true);
                opt.setAttribute("id", id);
                var sel = document.getElementsByName("' . $PA['itemName'] . '");
                for(var i=0; i < sel[0].options.length; i++){
                    optCheck = sel[0].options[i];
                    if(optCheck.value == id){
                        alert(opt.text + "' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('suggest-already-in-list','univie_pure') . '");
                        closeDiv();
                        return;
                    }
                }
                sel[0].appendChild(opt);
                closeDiv();
            }
            function closeDiv(){
                var parentDiv = document.getElementById("typo3-ucris-persons-suggest");
                parentDiv.setAttribute("style", "display:none");
            }
            </script>';

        return $output;
    }

    /*
     * Ajax call for backend choosing persons:
     * @param object \TYPO3\CMS\Backend\Form\FormEngine $tceForms Reference to an TCEforms instance
     * @return string $output
     */
    public function ajaxGetProject($PA, $fObj)
    {

        $query = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('query');

        if ($query) {
            $items = [];
            $xml ='<projectsQuery>
                        <size>100</size>
                        <locales>
                            <locale>' . $this->getLocale() . '</locale>
                        </locales>
                        <fields>
                            <field>acronym</field>
                            <field>uuid</field>
                            <field>title.*</field>
                        </fields>
                        <searchString>' . $query . '</searchString>
                    </projectsQuery>';
            $webservice = new WebService;
            $projects = $webservice->getJson('projects',$xml);
            foreach ($projects['items'] as $project) {
                $title = $project['title']['text'][0]['value'];
                if (isset($project['acronym'])) {
                    $title .= ' - ' . $project['acronym'];
                }
                $item = [
                    '0' => $title,
                    '1' => $project['uuid'],
                ];
                array_push($items,$item);
            }
        }

        // Put the wizard into $output and return it
        $output = '<style>.typo3-TCEforms-suggest-resultlist li:hover{background-color:#ffb;}</style>
            <div style="margin-top: 8px; margin-left: 4px;">
            <input type="text" name="query" id="query" placeholder="'
                . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('suggest-placeholder','univie_pure') .
            '" autocomplete="off">';

        if (isset($items)) {
            $output .= '<div class="typo3-TCEforms-suggest-choices" id="typo3-ucris-projects-suggest">
            <ul class="typo3-TCEforms-suggest-resultlist">';
            foreach ($items as $item) {
                $output .= '<li onclick="addToList(this.id)" id="' . $item[1] . '">
                        <span class="suggest-label"><span title="' . $item[0] . '">' . $item[0] . '</span></span><br><span class="suggest-uid">' . $item[1] . '</span>
                    </li>';
            }
            $output .= '</ul></div>';
        }

        $output .= '</div>
            <script>
            function addToList(id){
                var x = document.getElementById(id);
                var nodeList = x.childNodes;
                var opt = document.createElement("option");
                opt.value = id;
                opt.text = nodeList[1].innerText;
                opt.setAttribute("selected", true);
                opt.setAttribute("id", id);
                var sel = document.getElementsByName("' . $PA['itemName'] . '");
                for(var i=0; i < sel[0].options.length; i++){
                    optCheck = sel[0].options[i];
                    if(optCheck.value == id){
                        alert(opt.text + "' . \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('suggest-already-in-list','univie_pure') . '");
                        closeDiv();
                        return;
                    }
                }
                sel[0].appendChild(opt);
                closeDiv();
            }
            function closeDiv(){
                var parentDiv = document.getElementById("typo3-ucris-projects-suggest");
                parentDiv.setAttribute("style", "display:none");
            }
            </script>';

        return $output;
    }


    /**
     * Organisation from which publications should be displayed
     * @param object $config as reference
     */
    public function getOrganisations(array &$config)
    {
        $items = [];
        $postData = '<?xml version="1.0"?>
                        <organisationalUnitsQuery>
                            <size>300</size>
                            <locales><locale>' . $this->getLocale() . '</locale></locales>
                            <fields>
                                <field>uuid</field>
                                <field>name.text.value</field>
                            </fields>
                            <returnUsedContent>true</returnUsedContent>
                            <organisationalUnitPeriodStatus>ACTIVE</organisationalUnitPeriodStatus>
                        </organisationalUnitsQuery>
                        ';
        $webservice = new WebService;
        $organisations = $webservice->getJson('organisational-units', $postData);

        foreach ($organisations['items'] as $org) {
            $item = [
                '0' => $org['name']['text'][0]['value'],
                '1' => $org['uuid'],
            ];
            array_push($config['items'], $item);
        }
    }

    /*
     * Persons list for select user func:
     * @param object $config as reference
     */
    public function getPersons(array &$config)
    {
        $items = [];
        $persons = [];
        //$settings = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($config['row']['pi_flexform']);
        $personsList = $config['row']['settings.selectorPersons'];
        if ($personsList != '') {
            $persons = explode(',',$personsList);
        }

        $personXML = '<?xml version="1.0"?>
                <personsQuery><uuids>';
        if (count((array)$persons) > 0) {
            foreach ((array) $persons as $person) {
                if (strpos($person, "|")) {
                    $tmp = explode("|", $person);
                    $person = $tmp[0];
                }
                $personXML .= '<uuid>'. $person . '</uuid>';
            }

            $personXML .= '</uuids>
                        <size>20000</size>
                        <fields>
                            <field>uuid</field>
                            <field>name.*</field>
                        </fields>
                        <orderings>
                            <ordering>lastName</ordering>
                        </orderings>
                        <employmentStatus>ACTIVE</employmentStatus>
                    </personsQuery>';
            $webservice = new WebService;
            $persons = $webservice->getJson('persons',$personXML);
            foreach ($persons['items'] as $pers) {
                $item = [
                    '0' => $pers['name']['lastName'] . ', ' . $pers['name']['firstName'],
                    '1' => $pers['uuid'],
                ];
                array_push($config['items'],$item);
            }
        }
    }

    /*
     * Projects list for select project func
     * @param object $config as reference
     */
    public function getProjects(&$config)
    {
        $items = [];
        $projects = [];
        //$settings = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($config['row']['pi_flexform']);
        //$projectsList = $settings['data']['Common']['lDEF']['settings.selectorProjects']['vDEF'];
        $projectsList = $config['row']['settings.selectorProjects'];
        if ($projectsList != '') {
            $projects = explode(',',$projectsList);
        }

        $projectsXML = '<?xml version="1.0"?>
            <projectsQuery><uuids>';
        if (count((array) $projects) > 0) {
            foreach ((array) $projects as $project) {
                if (strpos($project, "|")) {
                    $tmp = explode("|", $project);
                    $project = $tmp[0];
                }
                $projectsXML .= '<uuid>'. $project . '</uuid>';
            }

            $projectsXML .= '</uuids>
                    <size>20000</size>
                    <locales>
                        <locale>' . $this->getLocale() . '</locale>
                    </locales>
                    <fields>
                        <field>uuid</field>
                        <field>acronym</field>
                        <field>title.*</field>
                    </fields>
                    <orderings>
                        <ordering>title</ordering>
                    </orderings>
                </projectsQuery>';
            $webservice = new WebService;
            $ucrisProjects = $webservice->getJson('projects',$projectsXML);
            foreach ($ucrisProjects['items'] as $proj) {
                $title = $proj['title']['text'][0]['value'];
                if ($proj['acronym']) {
                    $title .= ' - ' . $proj['acronym'];
                }
                $item = [
                    '0' => $title,
                    '1' => $proj['uuid'],
                ];
                array_push($config['items'],$item);
            }
        }
    }


    /**
     * structural query for publication types
     * @return String xml
     */
    public function getTypesFromPublications(array &$config)
    {
        $items = [];
        $classificationXML = '<?xml version="1.0"?>
                    <classificationSchemesQuery>
                      <size>300</size>
                      <locales><locale>' . $this->getLocale() . '</locale></locales>
                      <returnUsedContent>true</returnUsedContent>
                      <navigationLink>true</navigationLink>
                      <baseUri>' . self::RESEARCHOUTPUT . '</baseUri>
                    </classificationSchemesQuery>
                    ';
        $webservice = new WebService;
        $publicationTypes = $webservice->getJson('classification-schemes',$classificationXML);
        print_r($publicationTypes['items'][0]['containedClassifications'],1);
        $sorted = $this->sortClassification($publicationTypes);
        $this->sorted2items($sorted,$config);
    }

    /**
     * sort hierarchical
     */
    public function sorted2items($sorted,&$config)
    {
        foreach ($sorted as $optGroup) {
            $item = [
                '0' => '----- ' . $optGroup['title'] . ': -----',
                '1' => '--div--',
            ];
            array_push($config['items'],$item);
            foreach ($optGroup['child'] as $opt) {
                $item = [
                    '0' => $opt['title'],
                    '1' => $opt['uri'],
                ];
                array_push($config['items'],$item);
            }
        }
    }

    /**
     * structural query for activity types
     * @return String xml
     */
    public function getTypesFromActivities(&$config)
    {
        $items = array();
        $classificationXML = '<?xml version="1.0"?>
                    <classificationSchemesQuery>
                      <size>300</size>
                      <locales><locale>' . $this->getLocale() . '</locale></locales>
                      <returnUsedContent>true</returnUsedContent>
                      <navigationLink>true</navigationLink>
                      <baseUri>' . self::ACTIVITIES . '</baseUri>
                    </classificationSchemesQuery>
                    ';
        $webservice = new WebService;
        $activityTypes = $webservice->getJson('classification-schemes',$classificationXML);
        $sorted = $this->sortClassification($activityTypes);
        $this->sorted2items($sorted,$config);
    }

    /**
     * Sort classifications to hierarchical tree
     * first in api/511
     * @return array hierarchicalTree
     */
    public function sortClassification($unsorted)
    {
        $sorted = [];
        $i = 0;
        foreach ($unsorted['items'][0]['containedClassifications'] as $parent) {
            if (($parent['disabled'] != 1) && ($this->classificationHasChild($parent))) {
                $sorted[$i]['uri'] = $parent['uri'];
                $sorted[$i]['title'] = $parent['term']['text'][0]['value'];
                $j=0;
                foreach ($parent['classificationRelations'] as $child) {
                    if ($child['relationType']['uri'] == '/dk/atira/pure/core/hierarchies/child') {
                        if (!$this->isChildEnabledOnRootLevel($unsorted, $child['relatedTo']['uri'])) {
                            $c = [
                                $child['relatedTo']['uri'] => $child['relatedTo']['term']['text'][0]['value'],
                            ];
                            $sorted[$i]['child'][$j]['uri'] = $child['relatedTo']['uri'];
                            $sorted[$i]['child'][$j]['title'] = $child['relatedTo']['term']['text'][0]['value'];
                            $j++;
                        }
                    }
                }
                $i++;
            }
        }
        return $sorted;
    }

    /*
     * Check for children
     */
    public function classificationHasChild($parent)
    {
        $has = FALSE;
        if (array_key_exists('classificationRelations', $parent)) {
            foreach ($parent['classificationRelations'] as $child) {
                if ($child['relationType']['uri'] == '/dk/atira/pure/core/hierarchies/child') {
                    if ($child['relatedTo']['term']['text'][0]['value'] != '<placeholder>') {
                        $has = TRUE;
                        break;
                    }
                }
            }
        }
        return $has;
    }

    /*
     * Child is just a pointer to entry in root level. If disabled it is only visible on the root level:
     */
    public function isChildEnabledOnRootLevel($roots, $childUri)
    {
        foreach ($roots['items'][0]['containedClassifications'] as $root) {
            if ($root['uri'] == $childUri) return $root['disabled'];
        }
    }

    /**
     * structural query for press-media types
     * @return String xml
     */
    public function getTypesFromPressMedia(array &$config)
    {
        $items = [];
        $classificationXML = '<?xml version="1.0"?>
                    <classificationSchemesQuery>
                      <size>300</size>
                      <locales><locale>' . $this->getLocale() . '</locale></locales>
                      <returnUsedContent>true</returnUsedContent>
                      <navigationLink>true</navigationLink>
                      <baseUri>' . self::PRESSMEDIA . '</baseUri>
                    </classificationSchemesQuery>
                    ';

        $webservice = new WebService;
        $activityTypes = $webservice->getJson('classification-schemes',$classificationXML);
        foreach ($activityTypes['items']['0']['containedClassifications'] as $type) {
            $item = [
                '0' => $type['term']['text'][0]['value'],
                '1' => $type['uri'],
            ];
            array_push($config['items'],$item);
        }
    }

    /**
     * structural query for project types
     * @return String xml
     */
    public function getTypesFromProjects(&$config)
    {
        $items = [];
        $classificationXML = '<?xml version="1.0"?>
                    <classificationSchemesQuery>
                      <size>300</size>
                      <locales><locale>' . $this->getLocale() . '</locale></locales>
                      <returnUsedContent>true</returnUsedContent>
                      <navigationLink>true</navigationLink>
                      <baseUri>' . self::PROJECTS . '</baseUri>
                    </classificationSchemesQuery>
                    ';
        $webservice = new WebService;
        $projectsTypes = $webservice->getJson('classification-schemes',$classificationXML);
        foreach ($projectsTypes['items']['0']['containedClassifications'] as $type) {
            $item = [
                '0' => $type['value'],
                '1' => $type['uri'],
            ];
            array_push($config['items'],$item);
        }
    }

    /**
     * get uuid for email
     * @param $email
     * @return String uuid
     */
     public function getUuidForEmail($email)
     {
         $uuid = '123456789';//return some nonsens
         $xml = '<?xml version="1.0"?>
                <personsQuery>
                    <locales><locale>' . $this->getLocale() . '</locale></locales>
                    <fields><field>staffOrganisationAssociations.staffOrganisationAssociation.emails.email.value</field></fields>
                    <searchString>' . $email . '</searchString>
                </personsQuery>';
        $webservice = new WebService;
        $uuids = $webservice->getXml('persons',$xml);
        // One row returned for this email, we take the uuid:
        if ($uuids['count'] == 1) {
            $uuid = $uuids['items']['person']['@attributes']['uuid'];
        }
        //multiple rows returned, find the right one:
        elseif ($uuids['count'] > 1) {
            $persons = $uuids['items']['person'];
            foreach ($persons as $person) {
                foreach ($person['staffOrganisationAssociations']['staffOrganisationAssociation'] as $association) {
                    if ($association['emails']['email']['value'] == $email) {
                        $uuid = $person['@attributes']['uuid'];
                        break;
                    }
                }
            }
        }
        return $uuid;
     }

    /**
     * get uuid fpr i3v persons ID
     * @param $persId
     * @return $uuid
     */
    public function getUuidForPersId ($persId)
    {
        $uuid = 0;//return non existent
        $xml = '<?xml version="1.0"?>
            <personsQuery>
                <size>200</size>
                <fields>
                    <field>uuid</field>
                    <field>staffOrganisationAssociations.affiliationId</field>
                </fields>
                <personOrganisationAssociationTypes>
                    <associationType>STAFF</associationType>
                </personOrganisationAssociationTypes>
                <searchString>^' . $persId . '</searchString>
            </personsQuery>';
        $webservice = new WebService;
        $uuids = $webservice->getJson('persons',$xml);
        // results were found, find the right one:
        if ($uuids['count'] > 0) {
            foreach ($uuids['items'] as $item) {
                foreach ($item['staffOrganisationAssociations'] as $association) {
                    if ($association['affiliationId'] == $persId) {
                        $uuid = $item['uuid'];
                        break;
                    }
                }
            }
        }
        return $uuid;
    }

    /**
     * itemsProcFunc for TCA, show selector for Units, Persons, Projects for Research-Output, Units, Persons otherwise
     */
    public function getItemsToChoose(array &$config)
    {
        $selectUnits = [
            '0' => $GLOBALS['LANG']->sL('LLL:EXT:univie_pure/Resources/Private/Language/locallang_tca.xml:flexform.common.selectByUnit'),
            '1' => '0',
        ];
        \array_push($config['items'], $selectUnits);

        $selectPersons = [
            '0' => $GLOBALS['LANG']->sL('LLL:EXT:univie_pure/Resources/Private/Language/locallang_tca.xml:flexform.common.selectByPerson'),
            '1' => '1',
        ];
        \array_push($config['items'], $selectPersons);

        // Do this only for PUBLICATIONS:
        if ($config['flexParentDatabaseRow']['pi_flexform']['data']['sDEF']['lDEF']['settings.what_to_display']['vDEF'][0] == 'PUBLICATIONS') {
            $selectProjects = [
                '0' => $GLOBALS['LANG']->sL('LLL:EXT:univie_pure/Resources/Private/Language/locallang_tca.xml:flexform.common.selectByProject'),
                '1' => '2',
            ];
            \array_push($config['items'], $selectProjects);
        }

        $selectUuidList = [
            '0' => $GLOBALS['LANG']->sL('LLL:EXT:univie_pure/Resources/Private/Language/locallang_tca.xml:flexform.common.selectByUuidList'),
            '1' => '3',
        ];
        \array_push($config['items'], $selectUuidList);
    }
}
