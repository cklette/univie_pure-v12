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

class PureProjectWizard extends AbstractNode
{
    public function render()
    {
        $result = $this->initializeResultArray();
        //$fieldWizardResult = $this->renderFieldWizard();
        //$fieldWizardHtml = $fieldWizardResult['html'];
        //$resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldWizardResult, false);
        //$javaScriptModules = JavaScriptModuleInstruction::create('@univie/univiepure/');
        $javaScriptModules[0] = JavaScriptModuleInstruction::create('@univie/univiepure/search-ucris-project.js', 'ucris-project');
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
        /*
        $html[] = '<div id="pure-project-wizard-wrapper">';
        $html[] =          $fieldWizardHtml;
        //$html = '<input type="text" placeholder="search u:cris" id="ucris-search" />';
        $html[] = '</div>';
        */
        //$result['html'] = $html;
       // 'html' => implode(LF, $html);,

        $result = [
            'iconIdentifier' => 'actions-plus',
            'title' => 'u:cris Ajax projects',
            'html' => $html,
            'javaScriptModules' => $javaScriptModules,
            //'javaScriptModules' => ['@univie/univiepure/SearchUcris.js'],
            'linkAttributes' => [
                'class' => 'ucris-search-project',
                'data-id' => 'search-ucris-project',
                'id' => 'ucrisproject'
            ],
        ];
        $a = 0;
        return $result;
    }
}
