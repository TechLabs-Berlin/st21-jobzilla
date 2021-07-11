 <script>  
var ref_table_list_".$xmlObject['name']." =  ".'"'.B_form_helper::render_ref_table_options($ref_table_name, $ref_key, $ref_value).'"'."; 
 
 
$('document').ready(function(){ 
 
        $('.ref_select').select2({}); 
    }); 
                                 $('.add_rows_<?php echo $xmlObject['name']; ?>').on('click',function(){ 
                                  var count = parseInt($(this).attr('data-count')); 
                                  count= count+1; 
                                  var new_row = "<div class='col-md-12 ".'"+count+'.'"'."'><select style='display:inline; width:330px;' name='".$reference_key."[]' class='form-control select2 ref_select col-md-6'></select> &nbsp;&nbsp;&nbsp;&nbsp; <button type='button' style='display: inline;' count='".'"+count+'.'"'."' class='btn btn-danger remove_rows' onclick='remove_rows_".$xmlObject['name']."(".'"+count+'.'"'.")' value=''><i class='fa fa-remove'></i></button></div>".'";'."$('.eav_area".$xmlObject['name']."').append(new_row);$('.eav_area".$xmlObject['name']." .'+count+' .ref_select').html(ref_table_list_".$xmlObject['name']."); $('.eav_area".$xmlObject['name']." .'+count+' .ref_select').select2({}); $('.add_rows_".$xmlObject['name']."').attr('data-count',count); }); function remove_rows_".$xmlObject['name']."(count) { $('.eav_area".$xmlObject['name']." .'+count).remove(); }  
                                   
 </script> 
 