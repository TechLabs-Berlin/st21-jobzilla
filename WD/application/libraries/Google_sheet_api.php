<?php
/*
 * @author Ashrafuzzaman Sujan
 *
 */

// include composer dependencies
require (FCPATH . "/google_sheet_api_v4/vendor/autoload.php");

class Google_sheet_api{
    
    //member variable 
    protected $CI;
    protected $_appName;
    protected $_sheetID;
    
    /*
     * constructor
     */
    function __construct()
    {
        // Assign the CodeIgniter super-object
        $this->CI = & get_instance();

        //  assign values to member 
        $this->_appName = "portal-fxrate";
    }

    public function setSheetID($sheetID){
        $this->_sheetID = $sheetID;
    }
   
    public function sheetOperation($operation="set", $cellArray=array("id"), $tabName="vertical", $columnName="C", $columnNameTo="", $optParams= array() ){
      
      $client = new \Google_Client();
      $client->setApplicationName($this->_appName);
      $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
      $client->setAccessType('offline');
      $client->setAuthConfig(FCPATH . '/google_sheet_api_v4/portal-fxrate-9c8f97819b08.json');
      
      $sheets = new \Google_Service_Sheets($client);
      
      //response array
      $response = array('status'=>true);
      //data array
      $dataArray = array();
      //error array
      $errorArray = array();
      
      if (! isset($cellArray['id'])) {
            
            if ($operation == "set") {
                // update cell value
                $data = array();
                foreach ($cellArray as $position => $value) {                    
                    $data[] = [
                        'range' => "{$tabName}!".$position,
                        'majorDimension' => 'COLUMNS',
                        'values' => [
                            'values' => $value
                        ]
                    ];                    
                }
                
                try {
                    $requestBody = new Google_Service_Sheets_BatchUpdateValuesRequest([
                        'valueInputOption' => "USER_ENTERED",
                        'data' => $data
                    ]);
                    $resultData = $sheets->spreadsheets_values->batchUpdate($this->_sheetID, $requestBody);
                } catch (Exception $e) {
                    $response['status'] = false;
                    $errorArray[] = $e->getMessage() . "\n";
                }
                
            } elseif ($operation == "get") {
                // get cell value
                $range = "{$tabName}!{$columnName}:{$columnName}";
                if ( !empty( $columnNameTo ) )
                    $range = "{$tabName}!{$columnName}:{$columnNameTo}";
                try {
                    $resultData = $sheets->spreadsheets_values->get($this->_sheetID, $range, $optParams );                    
                    if(isset($resultData['values']) && empty($columnNameTo)){
                        $counter = 1;
                        foreach ($resultData['values'] as $row){
                            $dataArray["{$columnName}".$counter] = @$row;
                            $counter++;
                        }
                    }
                } catch (Exception $e) {
                    $response['status'] = false;
                    $errorArray[] = $e->getMessage() . "\n";
                }
            }
        }
      
      $response['resultData'] = @$resultData;
      $response['dataArray'] = $dataArray;
      $response['errorArray'] = $errorArray;
      return $response;
  }

}
