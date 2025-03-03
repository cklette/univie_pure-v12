<?php

declare(strict_types=1);

namespace Univie\UniviePure\Controller;

use Univie\UniviePure\Endpoints\ResearchOutput;
use Univie\UniviePure\Endpoints\Activities;
use Univie\UniviePure\Endpoints\PressMedia;
use Univie\UniviePure\Endpoints\Projects;
use Psr\Http\Message\ResponseInterface;
use Univie\UniviePure\Utility\Pager;

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
 * PublicationController
 */
class PureController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * Get settings from ConfigurationManager
     */
    public function initialize(): void
    {
        $this->settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );
    }

    /**
     * action list
     *
     * @return void
     */
    public function listAction(): ResponseInterface
    {
        //&tx_univiepure_univiepure[filter]=qwer
        //reduce the list:
        if ($this->request->hasArgument('filter')) {
            $this->settings['filter'] = $this->request->getArgument('filter');
            $this->view->assign('filter', $this->request->getArgument('filter'));
        }
        $language = $this->request->getAttribute('language');
        $locale = $language->getLocale()->getLanguageCode();
        //We always need the uid, pid of the plug-in:
        $cObjData = $this->request->getAttribute('currentContentObject')->data;
        $uid = $cObjData['uid'];
        $this->view->assign('cObjUid', $uid);
        $params = $this->request->getQueryParams();
        $this->settings['currentPage'] = 1;
        // Pager configuration, common settings:
        $configuration['itemsPerPage'] = intval($this->settings['pageSize']);
        $configuration['insertAbove'] = $this->settings['pager']['insertAbove'];
        $configuration['insertBelow'] = $this->settings['pager']['insertBelow'];
        $configuration['maximumVisiblePages'] = $this->settings['maximumVisiblePages'];
        $configuration['cObjUid'] = $uid;
        $configuration['currentPage'] = 1;
        if (isset($params['cobjuid'])) {
            $configuration['urlcObjUid'] = $params['cobjuid'];
        }
        // Change page only for the requested content element:
        if (isset($params['tx_univiepure_pi1']['cobjuid']) && $uid == $params['tx_univiepure_pi1']['cobjuid']) {
            $this->settings['currentPage'] = $params['tx_univiepure_pi1']['currentpage'];
            $configuration['currentPage'] = $params['tx_univiepure_pi1']['currentpage'];
        }

        switch ($this->settings['what_to_display']) {
            case 'PUBLICATIONS':
                $researchoutput = new ResearchOutput;
                $researchoutput->setLocale($locale);
                $view = $researchoutput->getPublicationList($this->settings, $uid);
                $this->view->assign('researchoutput', $view);
                break;
            case 'ACTIVITIES':
                $activities = new Activities;
                $view = $activities->getActivitiesList($this->settings, $uid);
                $this->view->assign('activities', $view);
                break;
            case 'PRESS-MEDIA':
                $pressmedia = new PressMedia;
                $view = $pressmedia->getPressMediaList($this->settings, $uid);
                $this->view->assign('pressMedia', $view);
                break;
            case 'PROJECTS':
                $projects = new Projects;
                $view = $projects->getProjectsList($this->settings, $uid);
                $this->view->assign('projects', $view);
                break;
            case 'DETAIL':
                //Should never occur
                break;
        }

        // Common, finish pager for each and every endpoint:
        $configuration['itemsCount'] = $view['count'];
        $pagination = new Pager($configuration);
        $paged = $pagination->buildPagination();
        $this->view->assign('pagination', $paged);

        return $this->htmlResponse();
    }

    /**
     * action show
     *
     * @param \Univie\UniviePure\Domain\Model\Publication $publication
     * @return void
     */
    public function showAction(): ResponseInterface
    {
        $arguments = $this->request->getArguments();

        switch ($arguments['what2show']) {
            case 'publ':
                $pub = new ResearchOutput;
                $view = $pub->getSinglePublication($arguments['uuid']);
                $this->view->assign('publication', $view);
                break;
            case 'act':
                //Should never occur. Is linked to portal
                break;
            case 'PRESS-MEDIA':
                //Should never occur. Is linked to portal
                break;
            case 'PROJECT':
                //Should never occur. Is linked to portal
                break;
        }
        return $this->htmlResponse();
    }
}
