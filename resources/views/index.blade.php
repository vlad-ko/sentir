<!DOCTYPE html>
<html>
    <head>
    	<link rel="stylesheet" href="https://cdn.concisecss.com/concise.min.css">

        <title>Sentir.io</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.js"></script>
        <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    </head>
    <body>

    	<main container>
    		<h1> Analyze text using Watson AI </h1>
	    	<section grid>
	    		<section column>
	    			<form>
	    				<textarea rows="15" id="text-to-analyze" name="text">{{ $text }}</textarea>
	    				<meta name="csrf-token" content="{{ csrf_token() }}">
	    		 		<button id="submit" class="-bordered -success">Analyze it!</button>
	    		 	</form>
    		 		<div>
    		 			<h4>History</h4>
    		 			<div class="previous-phrases"></div>
    		 		</div>
	    		</section>
	    		<section column>
	    			<h6>Current:</h6>
	    			<div class="current-text">{{ $text }}</div>
		    		<h6>Baseline:</h6>
	    			<div class="baseline-text"></div>
	    			<canvas id="my-chart" width="400" height="400"></canvas>
	    		</section>
	    	</section>
    	</main>

		<script>

		var ctx = document.getElementById("my-chart");
		window.myChart = new Chart(ctx, {
		    type: 'bar',
		    data: {
		        labels: ["Anger", "Disgust", "Fear", "Joy", "Sadness", "Analytical", "Confident", "Tentative", "Openness", "Conscientiousness", "Extraversion", "Agreeableness"],
		        datasets: [{
		            label: 'Current',
		            data: [],
		            backgroundColor: [
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(54, 162, 235, 0.2)'
		            ],
		            borderColor: [
		                'rgba(54, 162, 235, 1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(54, 162, 235, 1)'
		            ],
		            borderWidth: 1
		        },{
		        	label: 'Baseline',
		        	data: [],
		        	backgroundColor: [],
		        	borderColor: [],
		        	borderWidth: 1
		        }],
		    },
		    options: {
		        scales: {
		            yAxes: [{
		                ticks: {
		                    beginAtZero:true
		                }
		            }]
		        }
		    }
		});

		//doc ready / analyze
		$(document).ready(function() {
			loadPhrases();
			analyzePhrase();
			$("#submit").on('click', function(e) {
				e.preventDefault();
				analyzePhrase();
			});

			// click to baseline
			$(document).on("click", ".baseline, .current", function(e) {
				e.preventDefault();
				var dataIndex = 0;
				var id = $(this).data('id');
				var phraseId = "#phrase-" + id;
				var divToUpdate = '.current-text';
				if ($(this).attr('class') == 'baseline -bordered -success') {
					var dataIndex = 1;
					var divToUpdate = '.baseline-text';
				}
				$.ajax({
					  url: "/compare",
					  data: {
					    id: id
					  },
					  success: function( result ) {
					  	updateChart(result, dataIndex);
					  	$(divToUpdate).html($(phraseId).html());
					  }
					});
			});

			scrollPhrases();
		});

		var analyzePhrase = function() {
			$.ajax({
					url: "/analyze",
					method: "post",
					headers: {
        				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    				},
					data: {
						text: $("#text-to-analyze").val()
					},
					success: function( result ) {
						updateChart(result, 0);
						var result = $.parseJSON(result);
						$('#text-to-analyze').val(result.text);
						$('.current-text').html(result.text);
						html = buildHtml(result);
						$('.previous-phrases').prepend(html);
					}
				});
		}

		var updateChart = function(result, dataIndex) {
			var result = $.parseJSON(result);
			window.myChart.data.datasets[dataIndex].data[0] = result.anger;
		  	window.myChart.data.datasets[dataIndex].data[1] = result.disgust;
		  	window.myChart.data.datasets[dataIndex].data[2] = result.fear;
		  	window.myChart.data.datasets[dataIndex].data[3] = result.joy;
		  	window.myChart.data.datasets[dataIndex].data[4] = result.sadness;
		  	window.myChart.data.datasets[dataIndex].data[5] = result.analytical;
		  	window.myChart.data.datasets[dataIndex].data[6] = result.confident;
		  	window.myChart.data.datasets[dataIndex].data[7] = result.tentative;
		  	window.myChart.data.datasets[dataIndex].data[8] = result.openness;
		  	window.myChart.data.datasets[dataIndex].data[9] = result.conscientiousness;
		  	window.myChart.data.datasets[dataIndex].data[10] = result.extraversion;
		  	window.myChart.data.datasets[dataIndex].data[11] = result.agreeableness;
		  	window.myChart.update();
		}

		var scrollPhrases = function() {
			var win = $(window);

			// Each time the user scrolls
			win.scroll(function() {
				// End of the document reached?
				if ($(document).height() - win.height() == win.scrollTop()) {
					$('#loading').show();
					loadPhrases();
				}
			});
		}

		var loadPhrases = function() {
			$.ajax({
				url: '/load-phrases',
				method: 'post',
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function( results ) {
					prependHistory(results);
				}
			});
		}

		var prependHistory = function( results ) {
			console.log(results);
			var results = $.parseJSON(results);
			$.each(results, function(index, value) {
				html = buildHtml(value);
				$('.previous-phrases').prepend(html);
			});
		}

		var buildHtml = function ( htmlData ) {
			var html = '<p class="phrase" id="phrase-' + htmlData.id +'">' + htmlData.text + '</p><div class=".buttons"><button data-id="' + htmlData.id + '" class="current -bordered -success">Use as current</button> <button data-id="' + htmlData.id + '" class="baseline -bordered -success">Use as baseline</button></div>';

			return html;
		}

		</script>
    </body>
</html>
