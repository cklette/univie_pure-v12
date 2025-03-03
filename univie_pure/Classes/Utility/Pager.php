<?php

declare(strict_types=1);

namespace Univie\UniviePure\Utility;

/*
 * (c) 2023 Christian Klettner <christian.klettner@univie.ac.at>, univie
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

class Pager
{
    /**
     * @var array
     */
    protected $configuration = [
        'itemsPerPage' => 20,
        'insertAbove' => FALSE,
        'insertBelow' => TRUE,
        'maximumVisiblePages' => 3,
        'cObjUid' => 0,
        'itemsCount' => 0,
        'currentPage' => 1,
        'urlcObjUid' => null,
    ];

    /**
     * @var integer
     */
    protected $numberOfPages = 7;

    /**
     * Initialize Action of the widget controller
     *
     * @return void
     */
    public function __construct(array $pagerConfiguration)
    {
        $this->configuration = $pagerConfiguration;
    }

    /**
     * Returns the number of pages
     *
     * @return integer
     */
    public function getNumberOfPages()
    {
        return $this->numberOfPages;
    }

    /**
     * Sets the number of pages
     *
     * @param integer $numberOfPages the number of pages
     *
     * @return void
     */
    public function setNumberOfPages($numberOfPages)
    {
        $this->numberOfPages = $numberOfPages;
    }

    /**
     * Returns array $pagination
     *
     * @return array
     */
    public function buildPagination(): array
    {
        $this->setNumberOfPages(intval(ceil($this->configuration['itemsCount'] / (int)$this->configuration['itemsPerPage'])));
        $sidePageCount = intval($this->configuration['maximumVisiblePages']) >> 1;
        $firstPage = max($this->configuration['currentPage'] - $sidePageCount, 1);
        $lastPage = min($firstPage + $sidePageCount * 2, $this->getNumberOfPages());

        $pages = [];
        foreach (range($firstPage, $lastPage) as $index) {
            $pages[] = [
                'number' => $index,
                'isCurrent' => ($index == $this->configuration['currentPage'])
            ];
        }

        $total = $this->configuration['itemsCount'];
        $from = $this->configuration['itemsPerPage'] * ($this->configuration['currentPage'] - 1);
        $curr = $this->configuration['itemsPerPage'] * $this->configuration['currentPage'];
        $to = ($curr > $total) ? $total : $curr;
        $totalItems['totalItems'] = $total;
        //starting with 0, should be 1:
        $totalItems['from'] = $from + 1;
        $totalItems['to'] = $to;

        $pagination = [
            'pages' => $pages,
            'current' => $this->configuration['currentPage'],
            'numberOfPages' => $this->numberOfPages,
            'itemsPerPage' => $this->configuration['itemsPerPage'],
            'firstPage' => 1,
            'lastPage' => $this->numberOfPages,
            'isFirstPage' => ($this->configuration['currentPage'] == 1),
            'isLastPage' => ($this->configuration['currentPage'] == $this->numberOfPages),
            'cObjUid' => $this->configuration['cObjUid'],
            'from' => $totalItems['from'],
            'to' => $totalItems['to'],
            'total' => $total
        ];
        $pagination = $this->addPreviousAndNextPage($pagination);
        return $pagination;
    }

    /**
     * Adds the nextPage and the previousPage to the pagination array
     *
     * @param array $pagination the pagination array
     * @return array $pagination enhanced with previous/next
     */
    protected function addPreviousAndNextPage($pagination): array
    {
        if ($this->configuration['currentPage'] < $this->numberOfPages) {
            $pagination['nextPage'] = $this->configuration['currentPage'] + 1;
        }

        if ($this->configuration['currentPage'] > 1) {
            $pagination['previousPage'] = $this->configuration['currentPage'] - 1;
        }

        return $pagination;
    }
}
