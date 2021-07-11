<?php 
if ( ! defined('BASEPATH') ) die("No direct access allowed");

require 'vendor/autoload.php';

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class MsExcelApi
{
    public $tenantId;
    public $clientId;
    public $clientSecret;

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        // config
        $this->tenantId = "6ab273db-59a0-4b41-80f5-f37c1a4249de";
        $this->clientId = "a0bd530b-d398-4625-8451-2ffe6c72da20";
        $this->clientSecret = "qgzkEVCJ614]xptULC52)@?";
    }

    public function getTokenData ()
    {
        $guzzle = new \GuzzleHttp\Client();
        // $url = 'https://login.microsoftonline.com/'.$this->tenantId.'/oauth2/token?api-version=1.0';
        $url = 'https://login.microsoftonline.com/'.$this->tenantId.'/oauth2/v2.0/token/';
        return json_decode($guzzle->post($url, [
            'form_params' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ],
        ])->getBody()->getContents());
    }

    public function getAccessToken ()
    {
        return $this->getTokenData()->access_token;
    }

    public function getUserData ()
    {
        $accessToken = $this->getAccessToken();

        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        return $graph->createRequest("GET", "/users/")
                      ->setReturnType(Model\User::class)
                      ->execute();
    }

    public function getUserId ()
    {
        $userData = $this->getUserData();
        if ( !empty( $userData[0] ) ) return $userData[0]->getId();
        return null;
    }

    public function getWorkBookData()
    {
        $accessToken = $this->getAccessToken();
        $userId = $this->getUserId();

        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        return $graph->createRequest("GET", "/users/{$userId}/drive/root:/Mark Testing Graph API.xlsx:/")
                      ->setReturnType(Model\Workbook::class)
                      ->execute();
        // https://graph.microsoft.com/v1.0/users/85bd70ef-be7f-40c5-9342-0ca1d4002c41/drive/root:/Mark Testing Graph API.xlsx:/
    }

    public function getWorkBookId ()
    {
        $workbookData = $this->getWorkBookData();
        return $workbookData->getId();
    }

    public function updateWorkData ( $sheetName, $range, $bodyData )
    {
        $wbRangeData = new Model\WorkbookRange();
        $wbRangeData->setValues($bodyData);

        $accessToken = $this->getAccessToken();
        $userId = $this->getUserId();
        $wbId = $this->getWorkBookId();

        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        return $graph->createRequest("PATCH", "/users/{$userId}/drive/items/{$wbId}/workbook/worksheets/{$sheetName}/range(address='{$range}')")
                      ->attachBody( $wbRangeData )
                      ->setReturnType(Model\WorkbookRange::class)
                      ->execute();   
        // https://graph.microsoft.com/v1.0/users/85bd70ef-be7f-40c5-9342-0ca1d4002c41/drive/items/014V24AVUS3526J33WCZBJKH7E7UCTO4TA/workbook/worksheets/MarkTesting/range(address='B3')
    }

    public function getCellDataUsingRange ( $sheetName, $range )
    {
        $accessToken = $this->getAccessToken();
        $userId = $this->getUserId();
        $wbId = $this->getWorkBookId();

        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        return $graph->createRequest("GET", "/users/{$userId}/drive/items/{$wbId}/workbook/worksheets/{$sheetName}/range(address='{$range}')")
                      ->setReturnType(Model\WorkbookRange::class)
                      ->execute();   
    }

    public function getCellValues ( $sheetName, $range, $all=false )
    {
        $cellDataUsingRange = $this->getCellDataUsingRange( $sheetName, $range );
        $cellValues = $cellDataUsingRange->getValues();
        if ( $all ) return $cellValues;
        return $cellValues[0][0];
    }
}