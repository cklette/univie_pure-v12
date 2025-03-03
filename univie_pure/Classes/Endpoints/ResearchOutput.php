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
 * Get resarchoutput as entered in pure for a person, entity, project or fulltext search
 */
class ResearchOutput
{

    protected $locale;

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * produce xml for the list query of research-output
     * @param array $settings
     * @param string $cObjUid
     * @return array $publications
     */
    public function getPublicationList($settings, $cObjUid)
    {
        $xml = '<?xml version="1.0"?>
                    <researchOutputsQuery>';
        // If a project is entered as source for publications we get a list of uuids and have to enter them first:
        if ($settings['chooseSelector'] == 2) {
            $xml .= CommonUtilities::getProjectsXml($settings);
        }

        // If a list of UUIDs was entered:
        if ($settings['chooseSelector'] == 3) {
            $xml .= CommonUtilities::getUuidListXml($settings);
        }

        // page size:
        $xml .= CommonUtilities::getPageSize($settings['pageSize']);

        // offset:
        $xml .= CommonUtilities::getOffset($settings['pageSize'], $settings['currentPage']);

        // linking strategy:
        $xml .= '<linkingStrategy>portalLinkingStrategy</linkingStrategy>';

        // locale:
        $xml .= CommonUtilities::getLocale($this->locale);

        // rendering:
        $xml .= '<renderings><rendering>' . $settings['rendering'] . '</rendering></renderings>';

        // fields:
        // we need the current status just to check if it is something special (e.g. NOT published) and then display it
        $xml .= '<fields>';
        $xml .= '<field>uuid</field>';
        $xml .= '<field>publicationStatuses.current</field>';
        $xml .= '<field>publicationStatuses.publicationStatuses.*</field>';

        // fields for grouping:
        if ($settings['groupByYear'] == 1) {
            $xml .= $this->getFieldForGrouping();
        }

        // fields for publication type:
        $xml .= '<field>publicationStatuses.publicationStatus.*</field>';

        // fields for renderings:
        $xml .= '<field>renderings.*</field>';
        $xml .= '</fields>';

        // ordering:
        // backwardscompatibility:
        if (!array_key_exists('researchOutputOrdering', $settings) || strlen($settings['researchOutputOrdering']) == 0) $settings['researchOutputOrdering'] = '-publicationDate';
        if ($settings['researchOutputOrdering'] == 'publicationYear') $settings['researchOutputOrdering'] = 'publicationDate';
        if ($settings['researchOutputOrdering'] == '-publicationYear') $settings['researchOutputOrdering'] = '-publicationDate';
        $xml .= '<orderings><ordering>' . $settings['researchOutputOrdering'] . '</ordering></orderings>';

        // returnUsedContent:
        $xml .= '<returnUsedContent>true</returnUsedContent>';

        // navigationLink:
        $xml .= '<navigationLink>true</navigationLink>';

        // typeUris:
        if (($settings['narrowByPublicationType'] == 1) && ($settings['selectorPublicationType'] != '')) {
            $xml .= $this->getResearchTypesXml($settings['selectorPublicationType']);
        }

        // only status of published:
        if ($settings['publishedOnly'] == 1) {
            $xml .= '<publicationStatuses><publicationStatus>/dk/atira/pure/researchoutput/status/published</publicationStatus></publicationStatuses>';
        }

        // peer-reviewed:
        if ($settings['peerReviewedOnly'] == 1) {
            $xml .= '<peerReviews><peerReview>PEER_REVIEW</peerReview></peerReviews>';
        }

        // published before date:
        if ($settings['publishedBeforeDate']) {
            // includes the date! 2021-12-31 shows everything published before 2022-01-01!
            //temporary bug in java date, should be fixed in 521:
            //$xml .= '<publishedBeforeDate>' . $settings['publishedBeforeDate'] . 'T00:00:00.001Z</publishedBeforeDate>';
            $xml .= '<publishedBeforeDate>' . $settings['publishedBeforeDate'] . '</publishedBeforeDate>';
        }

        // published after date:
        if ($settings['publishedAfterDate']) {
            //temporary bug in java date, should be fixed in 521:
            //$xml .= '<publishedAfterDate>' . $settings['publishedAfterDate'] . 'T00:00:00.001Z</publishedAfterDate>';
            $xml .= '<publishedAfterDate>' . $settings['publishedAfterDate'] . '</publishedAfterDate>';
        }

        // workflowSteps - approved only:
        $xml .= '<workflowSteps>';
        $xml .= '<workflowStep>forApproval</workflowStep>';
        $xml .= '<workflowStep>approved</workflowStep>';
        $xml .= '</workflowSteps>';

        // either for organisations or for persons, both must not be submitted:
        $xml .= CommonUtilities::getPersonsOrOrganisationsXml($settings);

        // search AND filter:
        if (isset($settings['narrowBySearch']) || isset($settings['filter'])) {
            $xml .= $this->getSearchXml($settings);
        }

        $xml .= '</researchOutputsQuery>';
        $webservice = new WebService;
        $publications = $webservice->getJson('research-outputs', $xml);
        //reduce the array to year, status, rendering, uuid:
        $publications = $this->transformArray($publications, $settings);

        return $publications;
    }

    /*
     * Get the year for grouping
     * @return string $xml
     */
    public function getFieldForGrouping()
    {
        $xml = '<field>publicationStatuses.publicationDate.year</field>';
        return $xml;
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
        if (isset($settings['filter'])) {
            $terms .= ' ' . $settings['filter'];
        }
        $xml = '<searchString>' . trim($terms) . '</searchString>';
        return $xml;
    }

    /**
     * Query for classificationscheme
     * @param string $researchTypes
     * @return string xml
     */
    public function getResearchTypesXml($researchTypes)
    {
        $types = explode(',', $researchTypes);
        $xml = '<typeUris>';
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

    /**
     * Result set for manually chosen persons
     * @param string $personsList
     * @return string xml
     */
    public function getPersonsXml($personsList)
    {
        $xml = '<forPersons><uuids>';
        $persons = explode(',', $personsList);
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
     * Result set for organisational units
     * @param string $organisationsList
     * @return string xml
     */
    public function getOrganisationsXml($organisationList)
    {
        $xml = '<forOrganisationalUnits>';
        $organisations = explode(',', $organisationList);
        foreach ((array) $organisations as $org) {
            if (strpos($org, "|")) {
                $tmp = explode("|", $org);
                $org = $tmp[0];
            }
            $xml .= '<uuids>' . $org . '</uuids>';
        }
        $xml .= '</forOrganisationalUnits>';
        return $xml;
    }

    /**
     * restructure array: group by year
     * @param array $publications
     * @return array $array
     */
    public function groupByYear($publications)
    {
        $sortkey = $publications['contributionToJournal']['publicationStatuses']['publicationStatus']['publicationDate']['year'];
        $array = array();
        $array['count'] = $publications['count'];
        $i = 0;
        foreach ($publications['items'] as $contribution) {
            $array['contributionToJournal'][$i]['year'] = $contribution['publicationStatuses']['publicationDate']['year'];
            $array['contributionToJournal'][$i]['rendering'] = $contribution['rendering'][0]['value'];
            $array['contributionToJournal'][$i]['uuid'] = $contribution['uuid'];
            $i++;
        }
        return $array;
    }

    /**
     * restructure array
     * @param array $publications
     * @param array $settings
     * @return array $array
     */
    public function transformArray($publications, $settings)
    {
        $array = [];
        $array['count'] = $publications['count'];
        $i = 0;
        foreach ($publications['items'] as $contribution) {
            foreach ($contribution['publicationStatuses'] as $status) {
                if ($status['current'] == 'true') {
                    if ($settings['groupByYear']) {
                        $array['contributionToJournal'][$i]['year'] = $status['publicationDate']['year'];
                    }
                    if (isset($settings['showPublicationType'])) {
                        $array['contributionToJournal'][$i]['publicationStatus']['value'] = $status['publicationStatuses'][0]['value'];
                        $array['contributionToJournal'][$i]['publicationStatus']['uri'] = $status['publicationStatuses'][0]['uri'];
                    }
                }
            }
            $array['contributionToJournal'][$i]['rendering'] = $contribution['renderings'][0]['html'];
            $array['contributionToJournal'][$i]['uuid'] = $contribution['uuid'];
            $i++;
        }
        return $array;
    }

    /**
     * query for single publication
     * @param sting $uuid
     * @return string xml
     */
    public function getSinglePublication($uuid)
    {
        $xml = '<?xml version="1.0"?>
            <researchOutputsQuery>
            <uuids><uuid>' . $uuid . '</uuid></uuids>
            <linkingStrategy>portalLinkingStrategy</linkingStrategy>';

        // set locale:
        $xml .= CommonUtilities::getLocale();

        // and everything else:
        $xml .= '<returnUsedContent>false</returnUsedContent>
            <navigationLink>true</navigationLink>
            </researchOutputsQuery>';
        $webservice = new WebService;
        $publication = $webservice->getJson('research-outputs', $xml);
        // set the page title:
        $GLOBALS['TSFE']->page['title'] = $publication['items'][0]['title']['value'];
        return $publication;
    }
}
