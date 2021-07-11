

<?php $this->load->view("resume/header"); ?>

<!-- <div class="row" style="margin-bottom:20px">
	<div class="col-md-3">
		<div id="x">

		</div>
	</div> 
	<div class="col-md-9">
		<div class="row mypage">
			<div class="col-md-12">
				<br/>
				<div class="private"><a href="http://www.coolfreecv.com/privacy-policy" target="_blank" rel="nofollow"><u>Privacy policy while using the online wizard.</u></a>
				</div>
				<br/><br/>
				<form  action=""  method="post" enctype="multipart/form-data" style="font-size: 11px; font-family: verdana; "> 

					<div class="row"> 		 
						<div  class="col-md-12"  >	 

							<table style="width:85%">
								<tr>
									<td colspan="2" style="width:180px;"><h1>YOUR CONTACT DETAILS</h1></td>  
								</tr>
								<tr>
									<td style="width:180px;"><p>Name&nbsp;</p></td> <td><p><input type="text" name="name" id="name" class="inputstle" style="max-width:400px;"  /></p></td>
								</tr>
								<tr>
									<td style="width:180px;" valign="top"><p>Address&nbsp;</p></td> <td>
										<p>
											<input type="text" name="address"  id="address" class="inputstle" style="max-width:400px;" placeholder="Street Address, City, State/Province Zip"/></p>
										</td>
									</tr>


									<tr>
										<td><p>Phone</p></td> <td><p>
											<input type="text" name="phone"  id="phone" class="inputstle" style="max-width:400px;"/></p>
										</td>
									</tr>
									<tr>
										<td><p>Email</p></td> 
										<td><p>
											<input type="text" id="email" name="email" class="inputstle" style="max-width:400px;" /></p>
										</td>
									</tr>


								</table>
								<hr>

								<table style="width:85%">
									<tr>
										<td style="width:180px;"><p>Date &nbsp;</p></td> 
										<td><p>
											<input id="date" type="text" name="date" class="inputstle" style="max-width:400px;" value="02 July, 2021" /></p>
										</td>
									</tr> 


								</table>

								<hr>


								<table style="width:85%">

									<tr>
										<td style="width:180px;"><h1>RECIPIENT</h1></td> <td> &nbsp;</td>
									</tr>
									<tr>
										<td style="width:180px;"><p>Name of Person & Title &nbsp;</p></td> <td><p><input type="text" id="namerecipment" name="namerecipment" class="inputstle" style="max-width:400px;" /></p></td>
									</tr>
									<tr>
										<td><p>Company / Organization</p></td> <td><p>
											<input type="text" name="companyname"  id="companyname" class="inputstle" style="max-width:400px;"/></p>
										</td>
									</tr>
									<tr>
										<td valign="top"><p>Address</p></td> 
										<td>
											<textarea  style="max-width:400px; height:42px;" type="text" name="addressrecipment" class="inputstle" placeholder="Street Address, 
											City, State/Province Zip" />

										</textarea>
									</td>
								</tr>


							</table>

							
							<button class="btn btn-info" id="createCoverLetters" style="font-size:19px; font-weight:bold;">CREATE COVERLETTER</button>

						</div>
					</div>

					<div style="clear:both;"></div>

					<main class="content" id="genesis-content">
					</main>
				</textarea>

				<p><br/>
					<input value="DOWNLOAD PDF" type="Submit"  class="btn btn-info" style="font-size:19px; font-weight:bold;">
				</p>
			</form>
		</div>
	</div>
</div>
</div>




<div class="row" style="margin-top:30px">
	<div class="col-md-3"> 
	</div>
	<div class="col-md-9">


		<div class="row footerStyle">

			<div class="col-md-4 footerBorderLeft">
				<ul>
					<li>
						<a  class="footerLink" href="http://www.coolfreecv.com/skills-section-in-CV" rel="noreferrer">Skills section in CV</a>
					</li>
					<li>
						<a  class="footerLink" href="http://www.coolfreecv.com/professional-cv-tips" rel="noreferrer">Professional CV Tips</a>
					</li>
				</ul>
			</div>

			<div class="col-md-4 footerBorderLeft" >
				<ul>
					<li>
						<a class="footerLink" href="http://www.coolfreecv.com/interview" rel="noreferrer">How to prepare for a recruitment interview</a>
					</li> 
					<li>
						<a class="footerLink" href="http://www.coolfreecv.com/perfect-cv" rel="noreferrer">Perfect CV</a>
					</li>
				</ul>
			</div>

			<div class="col-md-4 footerBorderLeft">
			</div>
		</div>
	</div>

</div> -->

<!DOCTYPE html>

<style>
	html,
	body {
		height: 95% !important;
		margin: 0 !important;
	}

	footer {
		/*max-height: 5% !important;*/
		padding: 1em !important
	}

	section {
		min-height: 95% !important
	}

	.gen-box {
		background-color: #fff;
		display: block;
		padding: 1.25rem;
		color: #4a4a4a;
		font-size: 1rem;
		font-weight: 400;
		line-height: 1.25;
	}

	.gen-border {
		height: 0.25em;
		background-color: #d5d5d5;
	}

	.gen-box.warning {
		color: red;
		font-weight: 700;

	}

	.brand-text {
		display: flex;
		align-items: center;
		font-size: 1.5em
	}

	#extra-buttons {
		padding-top: 1em;
		margin-top: 1em;
		border-top: 0.5px solid #d5d5d5;
	}
</style>

<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>AI-Generated Cover Letters with GPT-2 using Google Cloud Run</title>
	<meta name="title" content="AI-Generated Cover letter with GPT-2 using Google Cloud Run" />
	<meta name="description" content="Generate Cover Letter from OpenAI's GPT-2 model!" />



	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.5/css/bulma.min.css">
	<script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
</head>


<body>
	<section id="main" class="section">
		<div class="container">
			<div class="columns is-variable is-5">
				<div class="column is-narrow" style="width: 300px;">
					<form id="gen-form">
						<div class="field">
							<label class="label">Text Prompt</label>
							<div class="control">
								<textarea id="prefix" class="textarea" type="text" placeholder="<|startoftext|>~[Machine learning, SQL, Python...]~Data Scientist" rows="3"></textarea>
							</div>
							<p class="help">Please insert required job skills and name of the position with specified syntax. Use ~ as delimeter. <em>(Optimal number of skills - from 3 to 10.)</em></p>
						</div>
						<div class="field">
							<label class="label">Cover Letter Length</label>
							<div class="control">
								<input id="length" class="input" type="text" placeholder="Text input" value="500">
							</div>
							<p class="help">Length of the text in tokens to generate. <em>(max: 1023)</em></p>
						</div>

						<div class="field">
							<label class="label">Temperature</label>
							<div class="control">
								<input id="temperature" class="input" type="text" placeholder="0.7" value="0.7">
							</div>
							<p class="help">Controls the generated cover letter "creativity." <em>(the higher the temperature, the more
							creative)</em></p>
						</div>
						<div class="field">
							<label class="label">Top <em>k</em></label>
							<div class="control">
								<input id="top_k" class="input" type="text" placeholder="40" value="40">
							</div>
							<p class="help">Constrains the generated text tokens to the top <em>k</em> possibilities. <em>(set to 0 to
							disable)</em></p>
						</div>
						<div class="buttons">
							<span class="control">
								<button type="submit" name="submit" id="generate-text" class="button is-link">
									<span class="icon">
										<i class="fas fa-md fa-pen"></i>
									</span><span>Generate Cover Letter!</span></button>
								</span>

							</div>
						</form>
						<div id="extra-buttons" class="buttons">
							<span class="control">
								<button id="save-image" class="button is-success">
									<span class="icon">
										<i class="fas fa-md fa-save"></i>
									</span><span>Save Image</span></button>
								</span>
								<span class="control">
									<button id="clear-text" class="button is-danger">
										<span class="icon">
											<i class="fas fa-md fa-trash-alt"></i>
										</span><span>Clear Texts</span</button> </span> </div> </div> <div id="model-output" class="column">
											<p id="tutorial" class="subtitle"><em>Generated text will appear here!
												Use the form to configure GPT-2 and press <strong>Generate Cover Letter</strong>
												to get your own text!
											</em></p>
										</div>
									</div>
								</div>


							</section>

							<footer class="footer">
								<div class="content has-text-centered">
									<p>
										Made using <a href="https://github.com/minimaxir/gpt-2-simple" target="_blank">gpt-2-simple</a>
										and trained with <a href="https://spacy.io" target="_blank">SpaCy</a>. Original GPT-2 model provided by <a
										href="https://openai.com" target="_blank">OpenAI</a>.
									</p>
								</div>
							</footer>


						</body>

						<script src="https://code.jquery.com/jquery-3.4.1.min.js"
						integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous">
					</script>

					<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js">
					</script>

					<script type="text/javascript">
						$(function () {
							$('#gen-form').submit(function (e) {
								e.preventDefault();
								$.ajax({
									type: "POST",
									url: "https://jzl-api-v7otpcjevq-lz.a.run.app",
									dataType: "json",
									data: JSON.stringify(getInputValues()),
									beforeSend: function (data) {
										$('#generate-text').addClass("is-loading");
										$('#generate-text').prop("disabled", true);
									},
									success: function (data) {
										$('#generate-text').removeClass("is-loading");
										$('#generate-text').prop("disabled", false);
										$('#tutorial').remove();
										var gentext = data.text;
										if ($("#prefix").length & $("#prefix").val() != '') {
											var pattern = new RegExp('^' + $("#prefix").val(), 'g');
											var gentext = gentext.replace(pattern, '<strong>' + $("#prefix").val() + '</strong>');
										}

										var gentext = gentext.replace(/\n\n/g, "<div><br></div>").replace(/\n/g, "<div></div>");
										var html = '<div class=\"gen-box\">' + gentext + '</div><div class="gen-border"></div>';
										$(html).appendTo('#model-output').hide().fadeIn("slow");
									},
									error: function (jqXHR, textStatus, errorThrown) {
										$('#generate-text').removeClass("is-loading");
										$('#generate-text').prop("disabled", false);
										$('#tutorial').remove();
										var html = '<div class="gen-box warning">There was an error generating the text! Please try again!</div><div class="gen-border"></div>';
										$(html).appendTo('#model-output').hide().fadeIn("slow");
									}
								});
							});
							$('#clear-text').click(function (e) {
								$('#model-output').text('')
							});

    // https://stackoverflow.com/a/51478809
    $("#save-image").click(function () {

    	html2canvas(document.querySelector('#model-output')).then(function (canvas) {

    		saveAs(canvas.toDataURL(), 'gen_texts.png');
    	});
    });

});

						function getInputValues() {
							var inputs = {};
							$("textarea, input").each(function () {
								inputs[$(this).attr('id')] = $(this).val();
							});
							return inputs;
						}

  // https://stackoverflow.com/a/51478809
  function saveAs(uri, filename) {

  	var link = document.createElement('a');

  	if (typeof link.download === 'string') {

  		link.href = uri;
  		link.download = filename;

      //Firefox requires the link to be in the body
      document.body.appendChild(link);

      //simulate click
      link.click();

      //remove the link when done
      document.body.removeChild(link);

  } else {

  	window.open(uri);

  }
}


</script>

</html>
<?php $this->load->view("resume/footer"); ?>

<script >

	var baseURL = "<?php echo base_url();?>";

	

	$(document).on("click", "#createCoverLetters", function(e){
		e.preventDefault();

		var name = $("#name").val();
		var namerecipment = $("#namerecipment").val();
		var address = $("#address").val();
		var phone = $("#phone").val();
		var email = $("#email").val();
		var date = $("#date").val();
		var companyname = $("#companyname").val();
		var addressrecipment = $("#addressrecipment").val();


		$.ajax({
			data: {name:name,namerecipment:namerecipment, address:address, phone:phone, email:email, date:date, companyname:companyname, addressrecipment:addressrecipment },
			url: baseURL+"dashboard/createCoverLetter",
			type: 'POST',
			success: function (response)
			{
				// var obj = JSON.parse(response);
				// console.log(obj);
				$("#genesis-content").html(response);

			}
		});
		
	})
	
</script>