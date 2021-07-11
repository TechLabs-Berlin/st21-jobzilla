

<?php $this->load->view("resume/header"); ?>

<div class="row" style="margin-bottom:20px">
	<div class="col-md-3">
		<div id="x">

		</div>
	</div> 
	<div class="col-md-9">
		<div class="row mypage">
			<div class="col-md-12">
				<br/>
				
				<br/><br/>
				<form  action="<?php echo base_url()."dashboard/uploadCv"?>"  method="post" enctype="multipart/form-data" style="font-size: 11px; font-family: verdana; "> 

					<div class="row"> 		 
						<div  class="col-md-12"  >	 

							<table style="width:85%">
								
								<tr>
									<td style="width:180px;"><p>
										<input value="UPLOAD CV" type="Submit"  class="btn btn-info" style="font-size:19px; font-weight:bold;">
									</p></td>
									<td><p>
										<input type="file" name="uploaded_cv" id="uploaded_cv" class="inputstle" style="max-width:400px;"  />
									</p>
								</td>
							</tr>


						</table>



					</div>
				</div>


				
			</p>
		</form>
	</div>
</div>
</div>
</div>



<?php $this->load->view("resume/footer"); ?>
