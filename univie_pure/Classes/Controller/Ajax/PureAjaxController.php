<?php

declare(strict_types=1);

namespace Univie\UniviePure\Controller\Ajax;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Univie\UniviePure\Service\WebService;

class PureAjaxController  extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    public function importUcrisPerson(ServerRequestInterface $request): ResponseInterface
    {


        $searchstring = $request->getQueryParams()['searchstring'];

        if ($searchstring) {
            $items = [];
            $xml = '<personsQuery>
                        <size>100</size>
                        <locales>
                            <locale>de_DE</locale>
                        </locales>
                        <fields>
                            <field>name.*</field>
                            <field>uuid</field>
                        </fields>
                        <searchString>' . $searchstring . '</searchString>
                    </personsQuery>';
            $webservice = new WebService;
            $persons = $webservice->getJson('persons', $xml);
            foreach ($persons['items'] as $pers) {
                //$p = $pers['name']['lastName'] . ', ' . $pers['name']['firstName'];
                $item = [
                    'name' => $pers['name']['lastName'] . ', ' . $pers['name']['firstName'],
                    'id' => $pers['uuid'],
                ];
                array_push($items, $item);
            }
        }
        $persons = json_encode($items);
        $val = json_validate($persons);
        return $this->jsonResponse($persons);
        /* return $this->jsonResponse(json_encode([
            $items,
            JSON_FORCE_OBJECT
        ])); */
    }

    public function importUcrisProject(ServerRequestInterface $request): ResponseInterface
    {
        $searchstring = $request->getQueryParams()['searchstring'];

        if ($searchstring) {
            $items = [];
            $xml ='<projectsQuery>
                        <size>100</size>
                        <locales>
                            <locale>de_DE</locale>
                        </locales>
                        <fields>
                            <field>acronym</field>
                            <field>uuid</field>
                            <field>title.*</field>
                        </fields>
                        <searchString>' . $searchstring . '</searchString>
                    </projectsQuery>';
            $webservice = new WebService;
            $projects = $webservice->getJson('projects', $xml);
            foreach ($projects['items'] as $proj) {
                //$p = $pers['name']['lastName'] . ', ' . $pers['name']['firstName'];
                $item = [
                    'name' => $proj['title']['text'][0]['value'],
                    'id' => $proj['uuid'],
                ];
                array_push($items, $item);
            }
        }

        $projects = json_encode($items);
        $val = json_validate($projects);
        return $this->jsonResponse($projects);

    }
}
