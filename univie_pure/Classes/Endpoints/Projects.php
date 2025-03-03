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
 * get a list of projects for a person or entity
 */
class Projects
{

    /**
     * produce xml for the list query of projects
     * @param array $settings
     * @param string $cobjUid
     * @return array $projects
     */
    public function getProjectsList($settings, $cObjUid)
    {

        $xml = '<?xml version="1.0"?>
            <projectsQuery>';

        // If a list of UUIDs was entered:
        if ($settings['chooseSelector'] == 3) {
            $xml .= CommonUtilities::getUuidListXml($settings);
        }

        // set page size:
        $xml .= CommonUtilities::getPageSize($settings['pageSize']);

        // set offset:
        // with params pagesize, uid of content element, pagername
        $xml .= CommonUtilities::getOffset($settings['pageSize'], $settings['currentPage']);

        // linkingStrategy:
        $xml .= '<linkingStrategy>portalLinkingStrategy</linkingStrategy>';

        // set locale:
        $xml .= CommonUtilities::getLocale();

        //rendering:
        $xml .= '<renderings><rendering>short</rendering></renderings>';

        // set ordering:
        $xml .= $this->getOrderingXml($settings['orderProjects']);

        // set filter:
        $xml .= $this->getFilterXml($settings['filterProjects']);

        // either for organisations or for persons, both must not be submitted:
        $xml .= CommonUtilities::getPersonsOrOrganisationsXml($settings);

        $xml .= '</projectsQuery>';

        $webservice = new WebService;
        $projects = $webservice->getJson('projects', $xml);

        return $projects;
    }

    /**
     * set the ordering
     * @param string $order
     * @return string $xml
     */
    public function getOrderingXml($order)
    {
        // set a default ordering:
        if (!$order) {
            $order = '-startDate';
        }
        $xml = '<orderings><ordering>' . $order . '</ordering></orderings>';
        return $xml;
    }

    /**
     * set the filter
     * @param string $filter
     * @return string $xml
     */
    public function getFilterXml($filter)
    {
        $xml = '';
        if ($filter) {
            $xml = '<projectStatus>' . $filter . '</projectStatus>';
        }
        return $xml;
    }
}
