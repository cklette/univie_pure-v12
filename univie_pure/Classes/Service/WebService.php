<?php

declare(strict_types=1);

namespace Univie\UniviePure\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2021 Christian Klettner <christian.klettner@univie.ac.at>, univie
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class handling the REST service of the pure installation
 */
class WebService
{

    /**
     * @var string
     */
    protected $server = '';

    /**
     * @var string
     */
    protected $apiKey = '';

    /**
     * @var string
     */
    protected $versionPath = '';

    //private readonly ExtensionConfiguration $extensionConfiguration;

    /**
     * init
     */
    public function __construct()
    {
        $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('univie_pure');
        $this->setServer($extensionConfiguration['pure_server']);
        $this->setApiKey($extensionConfiguration['apiKey']);
        $this->setVersionPath($extensionConfiguration['versionPath']);
    }

    /**
     * the call to the web service
     * @param string $endpoint e.g. Researchoutput
     * @param string $data_string the xml used for the call
     * @param string $responseType xml or json
     * @return string $response json or XML
     */
    public function getResponse($endpoint, $data_string, $responseType): string
    {
        //curl -X GET --header 'Accept: application/xml' --header 'api-key: 751734f0-a671-4183-8865-dbd771042b46' 'https://cris-entw.univie.ac.at/ws/api/59/research-outputs-meta/orderings'
        $url = $this->getServer() . $this->getVersionPath() . $endpoint;
        $headers = array("api-key: " . $this->getApiKey() . "", "Content-Type: application/xml", "Accept: application/" . $responseType . "", "charset=utf-8");
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_PRIVATE, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        //TODO: check for errors
        if (isset($response['code'])) {
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(
                FlashMessageQueue::NOTIFICATION_QUEUE
            );
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                'Empty or no resultset from pure Server for endpoint ' . $endpoint . ', Error: ' . $info['code'],
                'Query failed',
                ContextualFeedbackSeverity::ERROR
            );
            $notificationQueue->enqueue($flashMessage);
        }
        curl_close($ch);
        return $response;
    }

    /**
     * request a json result
     * @param string $endpoint
     * @param string $data_string
     * @return array $result
     */
    public function getJson($endpoint, $data_string): array
    {
        $json = $this->getResponse($endpoint, $data_string, 'json');
        $result = json_decode($json, TRUE);
        return $result;
    }

    /**
     * request a xml result
     * @param string $endpoint
     * @param string $data_string
     * @return array $result
     */
    public function getXml($endpoint, $data_string): array
    {
        $xmlResult = $this->getResponse($endpoint, $data_string, 'xml');
        $xml = simplexml_load_string($xmlResult, null, LIBXML_PEDANTIC);
        $result = json_decode(json_encode((array) $xml), 1);
        return $result;
    }

    /**
     * setter for server
     * @param string $server
     * @return void
     */
    private function setServer($server): void
    {
        $this->server = $server;
    }

    /** getter for server
     * @return string server
     */
    private function getServer(): string
    {
        return $this->server;
    }

    /**
     * setter for apiKey
     * @param string $apiKey
     * @return void
     */
    private function setApiKey($apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * getter for apiKey
     * @return string $apiKey
     */
    private function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * setter for version path e.g. /ws/api/59/
     * @param string $versionPath
     * @return void
     */
    private function setVersionPath($versionPath): void
    {
        $this->versionPath = $versionPath;
    }

    /**
     * getter for version path
     * @return string $versionPath
     */
    private function getVersionPath()
    {
        return $this->versionPath;
    }
}
