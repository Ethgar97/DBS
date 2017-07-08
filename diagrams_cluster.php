<!DOCTYPE html>
<html>
	<?php
	$auswahl=$_GET['auswahl'];
	// connection to the database
	$dbconn = pg_connect("host=agdbs-edu01.imp.fu-berlin.de dbname=HCF_Election user=student password=password")
		or die('Verbindungsaufbau fehlgeschlagen: ' . pg_last_error());

	// depanding on the input do a diffrent SQL-Querry	
	if($auswahl==1)	{
		$query = 'SELECT *
		FROM hashtag
		';
	}else if($auswahl==2){
		$query = 'SELECT name1, name2, COUNT(*) AS count
		FROM hashtag_hashtag
		GROUP BY (name1,name2)
		ORDER BY count DESC	';
		
		//here two querrys are needet
		$query2 = 'SELECT *
		FROM hashtag
		';
		
		$result2 = pg_query($query2) or die('Abfrage fehlgeschlagen: ' . pg_last_error());
	}	
	
	$result = pg_query($query) or die('Abfrage fehlgeschlagen: ' . pg_last_error());
	
	?>
	<head>
		<meta charset="utf-8"></meta>
		<title>Zeige das Diagramm an</title>		
		<style type="text/css">
		  #network {
			right:0;
			left:0;
			top:50px;
			bottom:0;
			position:absolute;
		  }
		</style>
	</head>
<body>
	<?php 

	if($auswahl==1)	{
		include('k_means.php');
		$hashtags=array();
		//fetching from the querry result
		while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
			foreach ($line as $col_value) {
				array_push($hashtags,$col_value);
			}
		}
		//calculating the cluster
		$cluster=k_means($_GET['anz'],$hashtags);
		
		//setting the $json variable so that it can be outputtet later to get the Graph
		$json=array();
		$id=0;
		$count=0;		
		foreach($cluster as $clust_key => $clust_value){
			//a random Color
			$color=[rand(50,255),rand(50,255),rand(50,255)];
			$json['nodes'][$id]['id']=$clust_key;
			$json['nodes'][$id]['label']=$clust_key;
			//means are bigger than normal nodes
			$json['nodes'][$id]['size']=3;
			//arranging the means in a circle
			$json['nodes'][$id]['x']=cos(pi()*2*$count/$_GET['anz'])*$_GET['anz']*10;
			$json['nodes'][$id]['y']=sin(pi()*2*$count/$_GET['anz'])*$_GET['anz']*10;
			$json['nodes'][$id]['color']="rgb(".$color[0].",".$color[1].",".$color[2].")";
			$count++;
			$c_id=$id;
			$id++;
			foreach($clust_value as $key=>$value){
				if(!in_array($key,array_keys($cluster))){
					$json['nodes'][$id]['id']=$key;
					$json['nodes'][$id]['label']=$key;
					//normal nodes are smaller
					$json['nodes'][$id]['size']=1;
					//arranging the nodes in a circle around the corresponding means
					//whith an distance to it acording to their hamming distance to the mean
					$json['nodes'][$id]['x']=$json['nodes'][$c_id]['x']+cos(pi()*2*($id-$c_id)/count($clust_value))*$value*3;
					$json['nodes'][$id]['y']=$json['nodes'][$c_id]['y']+sin(pi()*2*($id-$c_id)/count($clust_value))*$value*3;
					//nodes get he same color as their corresponding mean
					$json['nodes'][$id]['color']="rgb(".$color[0].",".$color[1].",".$color[2].")";#
					//drawing edges between the mean and the node
					$json['edges'][$id]['id']=$id;				
					$json['edges'][$id]['source']=$clust_key;
					$json['edges'][$id]['target']=$key;
					$id++;
				}				
			}
		}
		//reinitialize the keys
		$json['edges']=array_values($json['edges']);
		//writing into a .json file
		$fp=fopen('data.json', 'w');
		fwrite($fp, json_encode($json));
		fclose($fp);
		?>
		<div id="network"></div>
		<script src="sigma.min.js"></script>
		<script src="sigma.parsers.json.min.js"></script>
		<script src="sigma.layout.forceAtlas2.min.js"></script>

		<script>
			function getParameterByName(name) {
			var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
			return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
			};
			//reading the .json file and drawing the graph
			sigma.parsers.json('data.json', {
				container: 'network'			
			},

			function(s) { //This function is passed an instance of Sigma s

				nodeId = parseInt(getParameterByName('node_id'));

				var selectedNode;

				s.graph.nodes().forEach(function(node, i, a) {
				  if (node.id == nodeId) {
					selectedNode = node;
					return;
				  }
				});
				
				if (selectedNode != undefined){
				  s.cameras[0].goTo({x:selectedNode['read_cam0:x'],y:selectedNode['read_cam0:y'],ratio:0.1});
				}
			}); 
		</script>
		<?php
		
	} else if($auswahl==2)	{
		//fetching from the querry result
		$pairs=array();
		while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
			foreach ($line as $col_value) {
				array_push($pairs,$line);
			}
		}		
		$hashtags=array();
		while ($line = pg_fetch_array($result2, null, PGSQL_ASSOC)) {
			foreach ($line as $col_value) {
				array_push($hashtags,$col_value);
			}
		}
		//creating an node for each Hashtag
		$id=0;
		foreach($hashtags as $tag){
			$json['nodes'][$id]['id']=$tag;
			$json['nodes'][$id]['label']=$tag;
			$json['nodes'][$id]['size']=1;
			$id++;			
		}
		//pairing corresponding hashtags together
		$id=0;
		foreach($pairs as $pair){
			$json['edges'][$id]['id']=$id;
			$json['edges'][$id]['source']=$pair['name1'];
			$json['edges'][$id]['target']=$pair['name2'];
			$json['edges'][$id]['size']=$pair['count'];
			$id++;
		}
		//writing the .json file
		$fp=fopen('data2.json', 'w');
		fwrite($fp, json_encode($json));
		fclose($fp);
		?>
		<div id="network"></div>
		<script src="sigma.min.js"></script>
		<script src="sigma.parsers.json.min.js"></script>
		<script src="sigma.layout.forceAtlas2.min.js"></script>

		<script>
			function getParameterByName(name) {
			var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
			return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
			};
			//reading from the .json file
			sigma.parsers.json('data2.json', {
			container: 'network'
			},

			function(s) { //This function is passed an instance of Sigma s

				nodeId = parseInt(getParameterByName('node_id'));

				var selectedNode;

				s.graph.nodes().forEach(function(node, i, a) {
				  if (node.id == nodeId) {
					selectedNode = node;
					return;
				  }
				});
				
				//Initialize nodes as a circle
				s.graph.nodes().forEach(function(node, i, a) {
				  node.x = Math.cos(Math.PI * 2 * i / a.length);
				  node.y = Math.sin(Math.PI * 2 * i / a.length);
				}); 

				//Call refresh to render the new graph
				s.refresh();
				//arranging the nodes 
				s.startForceAtlas2({linLogMode:true});

				if (selectedNode != undefined){
				  s.cameras[0].goTo({x:selectedNode['read_cam0:x'],y:selectedNode['read_cam0:y'],ratio:0.1});
				}
				//stop arranging after a certain time to prevent flickering
				setTimeout(function() { s.stopForceAtlas2(); }, 4000)
			}); 
		</script>
		<?php
		
		}
		
		// Speicher freigeben
		pg_free_result($result);

		// Verbindung schließen
		pg_close($dbconn);
		?>
		<a href="index.php">zurück</a>
		
		
</body>
</html> 