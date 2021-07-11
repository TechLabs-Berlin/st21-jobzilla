<div class="history-section section-panel">
	<?php if ( empty(@$ismodal) ) { ?>
	<h3 class="m--margin-bottom-15"><?php echo dashboard_lang('_HISTORY')." "; ?></h3>
	<?php } ?>
	<span class="field-value">
		<div class="m-list-timeline">
			<div class="m-list-timeline__items">
				<?php  foreach ( $histories as $history ) { ?> 
					<div class="m-list-timeline__item">
						<span class="m-list-timeline__badge m-list-timeline__badge--brand"></span>
						<a href="<?php echo base_url("dbtables/event_log/edit/".$history["id"]); ?>" class="m-list-timeline__time jumptoeventlog" target="_blank" style="text-decoration: none;">
							<span class="history-date"><?php echo date("j M Y H:i", $history['time']); ?></span>
							<span class="history-text">
							<?php echo $history['message'] . ' ' . dashboard_lang("_BY") . ' ' . $history['first_name'].' '.$history['last_name']?>	</span>
						</a>		
					</div>
				<?php } ?>
			</div>
		</div>
		<?php 
		if ( sizeof($histories) == @$limit ) { ?>
			<a class="render-history-modal btn btn-accent m-btn m--margin-top-15" href="javascript:undefined;"><?php echo dashboard_lang('_MORE') ?></a>
		<?php } ?>		  
	</span>
</div>
       
    
    
