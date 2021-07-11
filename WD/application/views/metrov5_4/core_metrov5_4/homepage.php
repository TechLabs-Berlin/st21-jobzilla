<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<?php if($chart_exist):?>
<script src="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/js/amcharts.js"></script>
<script src="<?php echo CDN_URL; ?>media_metrov5_4/portal_core/js/serial.js"></script>
<?php for($i = 0; $i < count($chart); $i++) {?>

 <script>
            var chart<?php echo $i?>;

            var chartData<?php echo $i?> = <?php echo json_encode($chart[$i]);?>


            AmCharts.ready(function () {
                // SERIAL CHART
                chart<?php echo $i?> = new AmCharts.AmSerialChart();
                chart<?php echo $i?>.dataProvider = chartData<?php echo $i?>;
                chart<?php echo $i?>.categoryField = "label";
                chart<?php echo $i?>.startDuration = 1;

                // AXES
                // category
                var categoryAxis = chart<?php echo $i?>.categoryAxis;
                categoryAxis.labelRotation = 90;
                categoryAxis.gridPosition = "start";

                // value
                // in case you don't want to change default settings of value axis,
                // you don't need to create it, as one value axis is created automatically.

                // GRAPH
                var graph = new AmCharts.AmGraph();
                graph.valueField = "count";
                graph.balloonText = "[[category]]: <b>[[value]]</b>";
                graph.type = "column";
                graph.lineAlpha = 0;
                graph.fillAlphas = 0.8;
                chart<?php echo $i?>.addGraph(graph);

                // CURSOR
                var chartCursor = new AmCharts.ChartCursor();
                chartCursor.cursorAlpha = 0;
                chartCursor.zoomable = false;
                chartCursor.categoryBalloonEnabled = false;
                chart<?php echo $i?>.addChartCursor(chartCursor);

                chart<?php echo $i?>.creditsPosition = "top-right";

                chart<?php echo $i?>.write("chartdiv<?php echo $i?>");
            });
        </script>
        <?php } ?>
  <?php endif;?>
        
		<div id="msg" class="m-portlet">
		    <div class="m-portlet__head light bordered">
		    	
		    	<?php if($chart_exist):?>
		    		
		    		<?php for($i = 0; $i < count($chart); $i++) {
		    			$previous = $message[$i]['previous_unit'];
		    			$next = $message[$i]['next_unit'];
		    			
		    			$previous = ($previous == 0) ? dashboard_lang("_THIS") : dashboard_lang("_FROM_PREVIOUS")." ".$previous;
		    			$next = ($next == 0) ? dashboard_lang("_TO_THIS") : dashboard_lang("_TO_NEXT")." ".$next;
		    			
		    			echo $message[$i]['description']." "
		    			.$previous." ".$message[$i]['chart_period']." "
		    			.$next." ".$message[$i]['chart_period'];
		    			
		    		?>
		    			<div id="chartdiv<?php echo $i?>" style="width: 100%; height: 400px;"></div>
		    			<hr>
		    		<?php } ?>
	    		<?php else: ?>
		    		<?php echo $message;?>
		    	<?php endif;?>
		    </div>
		    
		    
		</div>
