<?php

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

$EM_CONF[$_EXTKEY] = [
    'title' => 'u:cris',
    'description' => 'Query the pure research information system (as installed at the university of Vienna) API > 514',
    'category' => 'plugin',
    'author' => 'Christian Klettner',
    'author_email' => 'christian.klettner@univie.ac.at',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '12.524.7',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0-12.9',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'Univie\\UniviePure\\' => 'Classes'
        ],
    ],
];
