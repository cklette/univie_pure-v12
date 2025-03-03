<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
   'univie-pure-pi1' => [
      'provider' => SvgIconProvider::class,
      'source' => 'EXT:univie_pure/Resources/Public/Icons/ucris.svg',
   ],
];
