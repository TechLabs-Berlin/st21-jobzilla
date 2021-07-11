

<?php $this->load->view("resume/header"); ?>



</br></br></br>
<?php foreach( $jobList as $eachJob){?>
	<div class="row" style="margin-bottom:20px">
		<div class="col-md-5">

			<div class="row mypage">
				<a href="<?php echo base_url()."dashboard/jobEdit/".$eachJob['id']?>"><h3><?php echo $eachJob['job_title'];?></h3></a>
			</br>
			<?php echo $eachJob['description'];?>
		</div> 
	</div>
</div>
<?php }?>



<?php $this->load->view("resume/footer"); ?>
