<!DOCTYPE html>
<html>
	<?php	
	$auswahl=$_GET['auswahl'];	
	// connection to the database
	$dbconn = pg_connect("host=agdbs-edu01.imp.fu-berlin.de dbname=HCF_Election user=student password=password")
		or die('Verbindungsaufbau fehlgeschlagen: ' . pg_last_error());
	// depanding on the input do a diffrent SQL-Querry	
	if($auswahl==3)	{
		$query = 'SELECT tweets.time,COUNT(tweets_hashtag.hashtag_name)
		FROM tweets, tweets_hashtag
		WHERE tweets.id=tweets_hashtag.tweet_id
		GROUP BY time
		ORDER BY time ASC
		';
	}else if ($auswahl==4){
		$query = 'SELECT tweets.time,COUNT(tweets_hashtag.hashtag_name)
		FROM tweets, tweets_hashtag
		WHERE tweets.id=tweets_hashtag.tweet_id
		AND tweets_hashtag.hashtag_name=\''.$_GET['hashtag'].'\'
		GROUP BY time
		ORDER BY time ASC
		';
	}
	$result = pg_query($query) or die('Abfrage fehlgeschlagen: ' . pg_last_error());
	
	?>
	<head>
		<meta charset="utf-8"></meta>
		<title>Zeige das Diagramm an</title>
		<!-- Style for the diagram -->
		<style type="text/css">
		  #barchart {
			right:0;
			left:0;
			top:50px;
			bottom:0;
			position:absolute;
		  }
		</style>
	</head>
<body>
	  <div id="barchart"></div> 
	  <!-- making imports -->
      <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.js"></script>      
      <script>
		<?php
		//setting the data for the diagram, output to JS
		echo ('var tags = [');
		$daten=array();
		$i=0;
		while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
			$daten[$i]=substr($line['time'],5);			
			echo('['.$i.', '.$line['count'].'], ');
			$i++;
		}
		echo ('];
		');
		//When there is a lot of Data not all dats are shown on the x-axis
		if(count($daten)<50){
			echo ('var daten = [');
			foreach($daten as $key=>$value){
				$temp=explode('-',$value);
				echo('['.$key.', "'.$temp[1].'. '.$temp[0].'"], ');
			}
			echo ('];
			');
		}else if(count($daten)<100){
			echo ('var daten = [');
			foreach($daten as $key=>$value){
				if($key%2==0){
					$temp=explode('-',$value);
				echo('['.$key.', "'.$temp[1].'. '.$temp[0].'"], ');
				}				
			}
			echo ('];
			');
		}else{
			echo ('var daten = [');
			foreach($daten as $key=>$value){
				if($key%4==0){
					$temp=explode('-',$value);
				echo('['.$key.', "'.$temp[1].'. '.$temp[0].'"], ');
				}				
			}
			echo ('];
			');
		}
		
		?>
		//the data
          var datenquelle = [
            { data: tags, color: "#0066ff" }
          ];
		  //making options
		  var optionen = {
             series: {
                bars: { show:true }
             },
            bars: {
               align: "center",
                barWidth: 0.75
             },
             xaxis: {
                ticks: daten,
             }
			 
          }
		  //the ploting
		  $.plot($("#barchart"), datenquelle, optionen);
		</script>  
		<a href="index.php">zurück</a><?php 
		
		if($auswahl == 4){echo(" gewählter Hashtag: ".$_GET['hashtag']);}		
		
		// Speicher freigeben
		pg_free_result($result);

		// Verbindung schließen
		pg_close($dbconn);
		?>
</body>
</html> 