<!DOCTYPE html>
<html>
	<head><?php
		// Database connection to get all the hashtags
		$dbconn = pg_connect("host=agdbs-edu01.imp.fu-berlin.de dbname=HCF_Election user=student password=password")
		or die('Verbindungsaufbau fehlgeschlagen: ' . pg_last_error());
		
		$query = 'SELECT *
			FROM hashtag
			ORDER BY name ASC
			';
			
		$result = pg_query($query) or die('Abfrage fehlgeschlagen: ' . pg_last_error());
		$tags=array();
		while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
			array_push($tags, $line['name']);
		}
		?>
		<meta charset="utf-8"></meta>
		<title>Wählen die Präsentation</title>
		<script>
			//is called when a checkbox is pressed
			function check(){
				document.getElementById("botton").disabled=false;				
				if(document.getElementById("1").checked ){
					document.getElementById("show").innerHTML = "Wählen sie die Anzahl der Cluster:<br><input name=\"anz\" type=\"text\"><i>(es muss eine Zahl zwischen 1 und 430 sein)</i>";
					document.getElementById("form").setAttribute("action", "diagrams_cluster.php");
					document.getElementById("botton").innerHTML = "Graph anzeigen";
				}else if(document.getElementById("2").checked ){
					document.getElementById("show").innerHTML = "";
					document.getElementById("form").setAttribute("action", "diagrams_cluster.php");
					document.getElementById("botton").innerHTML = "Graph anzeigen";
				}else if(document.getElementById("3").checked ){
					document.getElementById("show").innerHTML = "";					
					document.getElementById("form").setAttribute("action", "diagrams.php");
					document.getElementById("botton").innerHTML = "Diagram anzeigen";
				}else if(document.getElementById("4").checked ){					
					document.getElementById("show").innerHTML = "Wählen sie den Hashtag:<br><select name=\"hashtag\"><?php foreach($tags as $tag){echo('<option value=\"'.$tag.'\">'.$tag.'</option>');}?></select>";
					document.getElementById("form").setAttribute("action", "diagrams.php");
					document.getElementById("botton").innerHTML = "Diagram anzeigen";
				}
			}
		</script>
	</head>
<body>
	<form id="form" name="param" action="diagrams.php" method="get">
	  <p>
		Was soll betrachtet werden?<br>
		<fieldset>
		<input type="radio" id="1" value="1" name="auswahl" onChange="check()">
		<label for="1">Ähnlichkeit von Hashtags</label><br>	
		<input type="radio" id="2" value="2" name="auswahl" onChange="check()">
		<label for="1">Paarweise auftreten von Hashtags</label><br>
		<input type="radio" id="3" value="3" name="auswahl" onChange="check()">
		<label for="2">Häufigeit von den Hashtags</label><br>
		<input type="radio" id="4" value="4" name="auswahl" onChange="check()">
		<label for="2">Häufigeit von einem Hashtag</label><br>
		</fieldset>
		<div id="show"></div>	  
	  </p>	  
	<button id="botton" type="submit" disabled>
		anzeigen
	</button>
	</form>
</body>
</html> 