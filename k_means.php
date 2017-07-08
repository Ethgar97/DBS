<?php
//initializes the k-means Algorthim
function k_means($k, $hashtags){
	//the fist means are picked at random
	$means= array();
	$size=count($hashtags);
	for($i=0;$i<$k; $i++){
		do{
			$j=rand(0,$size-1);
		}while(isset($means[$j]));
	    $means[$j]=$hashtags[$j];
	}
	//the Array keys are reinitialized	
	$means=array_values($means);
	//starts the iteration
	return means_iterate($hashtags, $means, []);	
}
//itreate until the means are not changing anymore
function means_iterate($hashtags, $means, $abs){
	//calculates the distance between every node and the means
	$abs=array();
	foreach($means as $mean){
		foreach($hashtags as $tag){
			if(!isset($abs[$mean][$tag])){
				$abs[$mean][$tag]=hamming($mean,$tag);
			}			
		}
	}
	//put the nodes in the right cluster
	$cluster=array();
	foreach($hashtags as $tag){
		$courrentmean=$means[0];
		foreach($means as $mean){
			if($abs[$courrentmean][$tag]>$abs[$mean][$tag]){
				$courrentmean=$mean;
			}
		}
		$cluster[$courrentmean][$tag]=$abs[$courrentmean][$tag];		
	}
	//calculates the missing distances whithin the clusters
	foreach($cluster as $clust){
		$entries=array_keys($clust);
		foreach($entries as $entry1){
			foreach($entries as $entry2){
				if(!isset($abs[$entry1][$entry2])){
					$abs[$entry1][$entry2]=hamming($entry1,$entry2);
				}
				
			}
		}
	}
	//delets teh distances of the means to all but their cluster
	foreach($abs as $tag => $abstand){
		if(in_array($tag,$means)){
			foreach($abstand as $key=>$value){
				if(!isset($cluster[$tag][$key])){
					unset($abs[$tag][$key]);
				}
			}
		}
	}
	//calculates new means in each cluster
	//a mean is the node if he has the least distance to 
	//all other clusters in the node
	$newMean=array();
	$i=0;
	foreach($cluster as $key => $clust){
		$newMean[$i]=$key;
		$entries=array_keys($clust);
		foreach($entries as $entry){
			if(array_sum($abs[$newMean[$i]])>array_sum($abs[$entry])){
				$newMean[$i]=$entry;
			}
		}
		$i++;
	}	
	//checks if the means have changed
	if($newMean==$means){
		return $cluster;
	}else{
		return means_iterate($hashtags, $newMean, $abs);
	}
}

//calculates the hamming distance between two strings
function hamming($x,$y){
    if($x==""){
        return strlen($y);
    }else if($y==""){
		return strlen($x);
    }else if(substr($x,0,1)==substr($y,0,1)){
        return hamming(substr($x,1),substr($y,1));
    }else{
        return 1+hamming(substr($x,1),substr($y,1));
    }
} 









?>