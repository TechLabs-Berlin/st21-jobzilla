

<?php $this->load->view("resume/header"); ?>

<div class="row" style="margin-bottom:20px">
	<div class="col-md-3">
		<div id="x">

			<script async src="../pagead2.googlesyndication.com/pagead/js/f.txt"></script>
			<!-- banner300x600xCFC -->
			<ins class="adsbygoogle"
			style="display:inline-block;width:300px;height:600px"
			data-ad-client="ca-pub-8957905260244828"
			data-ad-slot="5638729864"></ins>
			<script>
				(adsbygoogle = window.adsbygoogle || []).push({});
			</script>


			&nbsp;
			<br/>
			&nbsp;
		</div> 
	</div> 
	<div class="col-md-9">
		<div class="row mypage">
			<div class="col-md-12">
				<br/>
				
				<form  action=<?php echo base_url()."dashboard/jobTitle" ;?> method="post" enctype="multipart/form-data" style="font-size: 11px; font-family: verdana; "> 

				<input type="hidden" name="id" class="inputstle" style="max-width:400px;"  value="<?php echo @$job['id']?>" />

					<div class="row"> 		 
						<div  class="col-md-12"  >	 

							<table style="width:85%">
								<tr>
									<td colspan="2" style="width:180px;"><h1>YOUR CONTACT DETAILS</h1></td>  <br/>
								</tr>
								<tr>
									<td style="width:180px;"><p>Title&nbsp;</p></td> <td><p>
									<input type="text" name="job_title" class="inputstle" style="max-width:400px;"  value="<?php echo @$job['job_title']?>" />
									</p></td>
								</tr>
								<tr>
									<td style="width:180px;"><p>Description&nbsp;</p>
									</td>
									<td>
										<textarea id="totu"  style="height:350px; width:85%;max-width:400px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px;" type="text" name="description"    value="" />
										<?php echo @$job['description']?>
									</textarea>

								</td>
							</tr>
						</table>

					</div>
				</div>


			</table>


			<p><br/>
				<input value="SAVE" type="Submit"  class="btn btn-info" style="font-size:19px; font-weight:bold;">
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

</div>


<?php $this->load->view("resume/footer"); ?>
