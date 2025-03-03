<?php

declare(strict_types=1);

namespace Univie\UniviePure\Utility;

use Univie\UniviePure\Utility\ClassificationScheme;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;

//https://docs.typo3.org/m/typo3/reference-coreapi/12.4/en-us/ApiOverview/FormEngine/Rendering/Index.html :
//
/*
$result = [
            'iconIdentifier' => 'import-data',
            'title' => $GLOBALS['LANG']->sL($this->langFile . ':pages.importData'),
            'linkAttributes' => [
                'class' => 'importData ',
                'data-id' => $this->data['databaseRow']['somefield'],
            ],
            'javaScriptModules' => ['@my_vendor/my_extension/import-data.js'],
        ];
*/

class PureWizard extends AbstractNode
{
    public function render()
    {
        $result = $this->initializeResultArray();
        //$javaScriptModules = JavaScriptModuleInstruction::create('@univie/univiepure/');
        $javaScriptModules[0] = JavaScriptModuleInstruction::create('@univie/univiepure/search-ucris-person.js', 'ucris-person');
        /*$config = [];
        $config['items'] = [];
        $d = $this->data['databaseRow']['pi_flexform'];
        $data = $this->data['databaseRow']['pi_flexform']['data']['Common']['lDEF']['settings.selectorPersons']['vDEF'];
        $config['row']['settings.selectorPersons'] = implode(',', $data);
        $cs = new ClassificationScheme;
        $cs->getPersons($config);
        $html = '<select name="persons" id="persons" multiple>';
        foreach ($config['items'] as $item) {
            $html .= '<option value="' . $item['1'] . '" selected="selected">' . $item['0'] . '</option>';
        }
        $html .= '</select>';*/
        //$html .= '<input placeholder="type" type="text">';
        $html = [];
        $html = '<input type="text" placeholder="search u:cris" id="ucris-search" />';
        //$result['html'] = $html;

        $result = [
            'iconIdentifier' => 'actions-plus',
            'title' => 'u:cris Ajax persons',
            'html' => $html,
            'javaScriptModules' => $javaScriptModules,
            //'javaScriptModules' => ['@univie/univiepure/SearchUcris.js'],
            'linkAttributes' => [
                'class' => 'ucris-search ',
                'data-id' => 'search-ucris',
                'id' => 'ucrisperson'
            ],
        ];
        $a = 0;
        return $result;
    }
}
