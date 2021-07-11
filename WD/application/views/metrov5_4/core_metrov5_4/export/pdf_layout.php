<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$dateFormat = $this->config->item('#DEFAULT_DATE_FORMAT');
$headArrayTranslated = array();
$head = (array) $rows[0];  
$head = array_keys($head);
$primaryFieldkey = array_search($primaryKey, $head);
unset($head[$primaryFieldkey]);
$headArray = [];
$allowedFields = [];

foreach ( $head as $eachColumn ) {

    if ( strpos ( $eachColumn, "_fkid" ) === false ) {

        $headArray[] = $eachColumn;
		$allowedFields[] =  $eachColumn;
    }
}
$counter = 0;
foreach ($headArray as $key => $item) {
    $prependTableName = (string) @$xmlObjectArray[$item]['prepend_table_name'];
    $translatedFieldName = '_'.strtoupper($item);
    if(!empty($prependTableName)){
        $translatedFieldName = '_'.strtoupper($prependTableName.'_'.$item);
    }
    $headArray[$key] = dashboard_lang($translatedFieldName);
    $counter++;
}

$perColWidth = 100 / $counter;

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title></title>
</head>
<style media="print">
</style>
<body>
<table border="1" cellspacing="0" cellpadding="5" style="font-family: arial; font-size: 13px; color: #333; width:100%;">
  <thead>
  	<tr>
  	<?php 
  	foreach ($headArray as $tableHeader){
  	    echo '<th align="left" style="width:'.$perColWidth.'%;">'.$tableHeader.'</th>';
  	}  	
  	?>      
    </tr>
  </thead>
  <tbody>    
	  <?php 

		$showTotal = TRUE;
		$totalHtml = '<tr>';
      	foreach ($rows as $row) {
      	    
      	    $row = (array) $row;
      	    echo "<tr>";
      	    foreach ($row as $colName => $value){
      	        
      	        if(in_array($colName, $allowedFields)){

					$is_translated = $xmlObjectArray[$colName]['is_translated'];
					if (! empty($is_translated) && $is_translated=="1") {
                        $value = dashboard_lang(strtoupper($value));
                    }
      	            
      	            $fieldType = $xmlObjectArray[$colName]['type'];
      	            $colAlign = 'left';
      	            if ($fieldType == 'datetime') {
      	                if(empty($value)){
      	                    $value = "";
      	                } else{
      	                    $value = date($dateFormat, $value);
      	                }
      	                
      	            } elseif ($fieldType == 'money') {
      	                $convertedValue = "";
      	                if(strlen($value) > 0){
      	                    $convertedValue = B_form_helper::customeMoneyFormate($value);
      	                }
      	                $value = $convertedValue;
      	                $colAlign = 'right';
      	            } elseif ($fieldType == 'number') {
      	                $colAlign = 'right';
      	            } elseif ($fieldType == "image") {
      	            
      	                if(!empty($value)){
      	                    $imageFolder = $this->config->item('img_upload_path');
      	                    $subDirectory = (string) $xmlObjectArray[$colName]['sub_directory'];
      	                    if (isset($subDirectory) and ! empty($subDirectory)) {
      	                        $imageFolder .= $subDirectory . "/";
      	                    }
      	                    $filePath = $imageFolder . $value;
      	                    if ((stripos($value, "http://") == false) || (stripos($value, "https://") == false)) {
      	                        if ($value != "" && file_exists(FCPATH . $filePath)) {
      	                            $value = CDN_URL . $filePath;
      	                        }
      	                    }
      	                     
      	                    $value = '<a target="_blank" href="' . $value . '" download>' . dashboard_lang("_DOWNLOAD_THIS_FILE") . '</a>';
      	                }
      	            
      	            } elseif ($fieldType == "file") {
      	            
      	                $imageFolder = $this->config->item('img_upload_path');
      	                if (! empty($value)) {
      	                    $value =  '<a target="_blank" href="' . CDN_URL . $imageFolder . $value . '" download>' . dashboard_lang("_DOWNLOAD_THIS_FILE") . '</a>';
      	                }
      	            }
      	            
					echo '<td align="'.$colAlign.'" style="width:'.$perColWidth.'%;">'.$value.'</td>';
					  
					if( $showTotal ){

						$value = '';
						$fieldName = @$xmlObjectArray[$colName]['name'];
						$totalValueCount = @$xmlObjectArray[$colName]['total_value_count'];
						$showCurrencySymbol = @$xmlObjectArray[$colName]['show_currency_symbol'];

						$value = getTotalColumnValue( $totalValueCount, $fieldType, $rows, $fieldName, $showCurrencySymbol );

						$totalHtml.= "<td align='right' >".$value."</td>";
					}
      	        }
      	        
      	    }
			  echo "</tr>";
			  $showTotal = FALSE;
		}
		if(!$showTotal){

			$totalHtml.= "</tr>";
			echo $totalHtml;
		}

      ?>   
  </tbody>
</table>
</body>
</html>