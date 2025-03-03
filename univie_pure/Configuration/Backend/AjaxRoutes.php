<?php

use Univie\UniviePure\Controller\Ajax\PureAjaxController;

return [

    'univiepure_searchpureproject' => [
        'path' => '/univiepure/pureproject',
        'target' => PureAjaxController::class . '::importUcrisProject',
    ],

    'univiepure_searchpureperson' => [
        'path' => '/univiepure/pureperson',
        'target' => PureAjaxController::class . '::importUcrisPerson',
    ],
];
