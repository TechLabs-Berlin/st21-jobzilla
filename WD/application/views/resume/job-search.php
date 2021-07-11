

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
			<h3>SKILLS</h3>
			<?php  echo $skills->skills;?>
			<div class="col-md-12">
				<br/>

				<form  action=<?php echo base_url()."dashboard/jobSearch" ;?> method="post" enctype="multipart/form-data" style="font-size: 11px; font-family: verdana; "> 

					<input type="hidden" name="id" class="inputstle" style="max-width:400px;"  value="<?php echo @$job['id']?>" />

					<div class="row"> 		 
						<div  class="col-md-12"  >	 

							<table style="width:85%">
								<tr>
									<td colspan="2" style="width:180px;"><h1>Job Search</h1></td>  <br/>
								</tr>
								<tr>
									<td style="width:180px;"><p>Title&nbsp;</p></td> <td><p>
										<input type="text" name="title" class="inputstle" style="max-width:400px;"  value="<?php echo @$post['title']?>" /><br/>
									</p></td>
								</tr>
								<tr>
									<td style="width:180px;"><p>Skills&nbsp;</p></td> <td><p>
										<input type="text" name="skill" class="inputstle" style="max-width:400px;"  value="<?php echo @$post['skill']?>" /><br/>
									</p></td>
								</tr>
								<tr>
									<td style="width:180px;"><p>City&nbsp;</p></td> <td><p>
										<input type="text" name="city" class="inputstle" style="max-width:400px;"  value="<?php echo @$post['city']?>" /><br/>
									</p></td>
								</tr>

							</table>

						</div>
					</div>


				</table>


				<p><br/>
					<input value="Find Jobs" type="Submit"  class="btn btn-info" style="font-size:19px; font-weight:bold;">
				</p>
			</form>
		</div>
	</div> 
</div>
</div>

</br></br></br>
<?php if(isset($results)){ foreach( $results as $eachJob){?>
	<div class="row" style="margin-bottom:20px">
		<div class="col-md-3">
		</div>
		<div class="col-md-9">

			<div class="row mypage">
				<a href="#"><h3><?php echo $eachJob['title'];?></h3></a>
			</br>
			<?php echo $eachJob['description'];?>
		</div> 
	</div>
</div>
<?php } }?>


<?php $this->load->view("resume/footer"); ?>
