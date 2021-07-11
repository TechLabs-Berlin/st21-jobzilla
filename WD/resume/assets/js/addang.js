$(function () {
    $(":file").change(function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = imageIsLoaded;
            reader.readAsDataURL(this.files[0]);
        }
    });
});

function imageIsLoaded(e) {
    $('#myImg').attr('src', e.target.result);
};



$(function() {
    $('div.input_fields_wrap').sortable({
        connectWith: 'div.input_fields_wrap',
        beforeStop: function(ev, ui) {
            if ($(ui.item).hasClass('hasItems') && $(ui.placeholder).parent()[0] != this) {
                $(this).sortable('cancel');
            }
        }
    });
    $('div.sublist').sortable({
        connectWith: 'div.sublist'
    });
});


$(function() {
    $('div.input_fields_wrap01').sortable({
        connectWith: 'div.input_fields_wrap01',
        beforeStop: function(ev, ui) {
            if ($(ui.item).hasClass('hasItems') && $(ui.placeholder).parent()[0] != this) {
                $(this).sortable('cancel');
            }
        }
    });
    $('div.sublist').sortable({
        connectWith: 'div.sublist'
    });
});

$(function() {
    $('div.input_fields_wrap02').sortable({
        connectWith: 'div.input_fields_wrap02',
        beforeStop: function(ev, ui) {
            if ($(ui.item).hasClass('hasItems') && $(ui.placeholder).parent()[0] != this) {
                $(this).sortable('cancel');
            }
        }
    });
    $('div.sublist').sortable({
        connectWith: 'div.sublist'
    });
});


$(document).ready(function() {
    var max_fields      = 15; //maximum input boxes allowed JOB
    var wrapper         = $(".input_fields_wrap"); //Fields wrapper
    var add_button      = $(".add_field_button"); //Add button ID
    
    var x = 1; //initlal text box count
	var i = 1;
    $(add_button).on("click",function(e){ //on add input button click
        e.preventDefault();
        if(x < max_fields){ //max input box allowed
            x++; //text box increment
			i++;
            $(wrapper).append('<div style="margin-bottom:20px; margin-top:20px;"><table style="padding-bottom:1px;"> <tr>  <td><p>Date &nbsp;</p></td> <td style="width:87%"><p><input type="text" name="job[]" class="inputstleang" /></p></td> </tr><tr>  <td><p>Employer, City &nbsp;</p></td> <td><p><input type="text" name="job[]"  class="inputstleang" /></p></td> </tr> <tr>  <td valign="top"><p><br/>Job Title,  &nbsp;<br/>Job Description</p></td> <td style="width:80%"><p><input type="button" value="Add a Bullet: •" onclick="bulet'+i+'()" /><br/><textarea id="text'+i+'" type="text" name="job[]" class="jobstyleang" /></textarea></p></td> </tr> </table> <img title="MOVE DOWN / UP"  src="//app.coolfreecv.com/images/arrow_drag.png" width="25" height="25" class="positionarrow" ><a title="DELETE" href="#" class="remove_field" style="margin-left:30px;"><img  src="//app.coolfreecv.com/images/remove.png" width="25" height="25" ></a></div>'); //add input box
        }
    });
    
    $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
	 
        e.preventDefault(); $(this).parent('div').remove();x--;
    })
});


// two function
$(document).ready(function() {
    var max_fields01      = 10; //maximum input boxes allowed SCHOOL
    var wrapper01         = $(".input_fields_wrap01"); //Fields wrapper
    var add_button01      = $(".add_field_button01"); //Add button ID
    
    var x = 1; //initlal text box count
	var i = 1;
    $(add_button01).on("click",function(a){ //on add input button click
        a.preventDefault();
        if(x < max_fields01){ //max input box allowed
            x++; //text box increment
			i++;
            $(wrapper01).append('<div style="margin-bottom:20px; margin-top:20px;"><table style="padding-bottom:1px;"> <tr>  <td><p>Date &nbsp;&nbsp; &nbsp; &nbsp;</p></td> <td><p><input type="text" name="school[]" class="inputstleang" /></p></td> </tr><tr>  <td><p>School, City &nbsp;</p></td> <td style="width:87%"><p><input type="text" name="school[]"  class="inputstleang" /></p></td> </tr> <tr>  <td valign="top"><p><br/>Degree,<br/> Description &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p></td> <td style="width:80%"><p><input type="button" value="Add a Bullet: •" onclick="kropka'+i+'()" /><br/><textarea id="zawartosc'+i+'" type="text" name="school[]" class="jobstyleang" /></textarea></p></td> </tr> </table> <img title="MOVE DOWN / UP"  src="//app.coolfreecv.com/images/arrow_drag.png" width="25" height="25"   class="positionarrow" ><a href="#" class="remove_field01" style="margin-left:30px;" title="DELETE"><img   src="//app.coolfreecv.com/images/remove.png" width="25" height="25" ></a> </div> '); //add input box
        }
    });
    
    $(wrapper01).on("click",".remove_field01", function(a){ //user click on remove text
        a.preventDefault(); $(this).parent('div').remove();x--;
    })
});


// tree function 
$(document).ready(function() {
    var max_fields02      = 10; //maximum input boxes allowed
    var wrapper02         = $(".input_fields_wrap02"); //Fields wrapper
    var add_button02      = $(".add_field_button02"); //Add button ID
    
    var x = 1; //initlal text box count
    $(add_button02).on("click",function(a){ //on add input button click
        a.preventDefault();
        if(x < max_fields02){ //max input box allowed
            x++; //text box increment
            $(wrapper02).append('<div style="margin-bottom:20px; margin-top:20px;"><div style="float:left; width:315px;"><p>Language&nbsp;<input type="text" name="sprache[]" class="inputstlesprache01" /></p></div> <div style="float:left; width:370px;"><p>Level &nbsp;<a class="dymekbig"><img src="//app.coolfreecv.com/images/info.png" style="vertical-align:middle;"><span><i style="color:#2E81B1;">Common European Framework:</i><br/><b>A1</b> – Beginner<br/><b>A2</b> – Pre-Intermediate<br/><b>B1</b> – Intermediate<br/><b>B2</b> – Upper-Intermediate<br/><b>C1</b> – Advanced<br/><b>C2</b> – Proficient</span></a>&nbsp;&nbsp;<input type="text" name="sprache[]" class="inputstlesprache" /></p></div><img title="MOVE DOWN / UP"  src="//app.coolfreecv.com/images/arrow_drag.png" width="25" height="25"   class="positionarrow" ><a href="#" class="remove_field02" style="margin-left:30px;" title="DELETE"><img   src="//app.coolfreecv.com/images/remove.png" width="25" height="25" ></a> </div>'); //add input box
        }
    });
    
    $(wrapper02).on("click",".remove_field02", function(a){ //user click on remove text
        a.preventDefault(); $(this).parent('div').remove(); x--;
    })
});



// add top function 
$(document).ready(function() {
    var max_fields_top      = 3; //maximum input boxes allowed
    var wrapper_top        = $(".input_fields_wrap_top"); //Fields wrapper
    var add_button_top     = $(".add_field_button_top"); //Add button ID
    
    var x = 0; //initlal text box count
    $(add_button_top).on("click",function(a){ //on add input button click
        a.preventDefault();
        if(x < max_fields_top){ //max input box allowed
            x++; //text box increment
            $(wrapper_top).append('<div class="row"><div class="col-md-3"> Label:<br/><input type="text" name="new[]" class="inputstle" style="max-width:150px;" /></div><div class="col-md-7"> Content:<br/><input type="text" name="new[]" class="inputstle" style="max-width:400px;" /><a href="#" class="remove_field_top" style="margin-left:30px;" title="DELETE"><br/></div> <br/><img   src="//app.coolfreecv.com/images/remove.png" width="25" height="25" ></a></div>'); //add input box
        }
    });
    
    $(wrapper_top).on("click",".remove_field_top", function(a){ //user click on remove text
        a.preventDefault(); $(this).parent('div').remove(); x--;
    })
});



// add button PHOTO
$(document).ready(function() {
    var max_fields_top      = 1; //maximum input boxes allowed
    var wrapper_top        = $(".input_fields"); //Fields wrapper
    var add_button_top     = $(".add_photo"); //Add button ID
    
    var x = 0; //initlal text box count
    $(add_button_top).on("click",function(a){ //on add input button click
        a.preventDefault();
        if(x < max_fields_top){ //max input box allowed
            x++; //text box increment
            $(wrapper_top).append('<input type="button" value="Delete Photo" onclick="cleanphoto()" class="remove_field_top" />'); //add input box
        }
    });
    
    $(wrapper_top).on("click",".remove_field_top", function(a){ //user click on remove text
        a.preventDefault(); $(this).parent('input').remove(); x--;
    })
});
