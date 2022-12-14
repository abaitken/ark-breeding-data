<?php
header("Content-Type: application/json");

function human_filesize($bytes, $decimals = 2) {
    $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $size[$factor];
}


$parts = parse_url($_SERVER['REQUEST_URI']);
parse_str($parts['query'], $query);

$action = 'list';

if(array_key_exists('action', $query))
    $action = $query['action'];

switch ($action) {
    case 'list':
        $mapContents = file_get_contents("../data/maps.json");
        $mapData = json_decode($mapContents, true);
        
        $result = array();
        
        // SCOPE
        {
            $item = array();
            $item["text"] = "All merged";
            $item["filename"] = "cluster.asb";
            $item["url"] = "api/index.php?action=download-merged";
            $item["modified"] = date(DATE_ATOM, time());
            $item["filesize"] = "-";
            
            array_push($result, $item);
        }
    
    	$defaultSize = filesize("../data/default.asb");
    	$total = 0;
        $clusterModified = 0;
        
        foreach($mapData as $map) {
            $hidden = $map["Hidden"];
            if($hidden) 
                continue;
            
            $id = $map["Id"];
            
            if(!file_exists("../data/" . $id . ".asb"))
                continue;
            
            $item = array();
            $item["text"] = $map["Text"];
            $item["filename"] = $id . ".asb";
            $item["url"] = "api/index.php?action=download&id=" . $id;
            $modified = filemtime("../data/" . $id . ".asb");
            $clusterModified = $clusterModified > $modified ? $clusterModified : $modified;
            
            $item["modified"] = date(DATE_ATOM, $modified);
          	$filesize = filesize("../data/" . $id . ".asb");
          	$total += ($filesize - 1);
            $item["filesize"] = human_filesize($filesize, 2);
            
            array_push($result, $item);
        }
    
    	$total += $defaultSize;
    	$result[0]["filesize"] = '~' . human_filesize($total, 2);
        $result[0]["modified"] = date(DATE_ATOM, $clusterModified);
        
        echo json_encode($result);
        
        break;
    case 'download':
        if(!array_key_exists('id', $query))
        {
            echo "{}";
            break;
        }

        if(preg_match('/[/\.]/', $query['id']))
        {
            echo "{}";
            break;
        }
            
        if(file_exists("../data/" . $id . ".asb"))
        {
            echo "{}";
            break;
        }
        
        $id = $query['id'];
        $contents = file_get_contents("../data/" . $id . ".asb");
        
        echo $contents;
        
        break;
    case 'download-merged':
        
        $defaultContents = file_get_contents("../data/default.asb");
        $result = json_decode($defaultContents, true);
        
        $mapContents = file_get_contents("../data/maps.json");
        $mapData = json_decode($mapContents, true);
        
        foreach($mapData as $map) {
            $hidden = $map["Hidden"];
            if($hidden) 
                continue;
            
            $id = $map["Id"];
            
            if(!file_exists("../data/" . $id . ".asb"))
                continue;
          
            $contents = file_get_contents("../data/" . $id . ".asb");
            $breedingData = json_decode($contents, true);
            
            $creatures = $breedingData["creatures"];
            foreach($creatures as $item) {
                array_push($result["creatures"], $item);
            }
            $players = $breedingData["players"];
            foreach($players as $item) {
                array_push($result["players"], $item);
            }
            $tribes = $breedingData["tribes"];
            foreach($tribes as $item) {
                array_push($result["tribes"], $item);
            }
        }
        
        echo json_encode($result);
        
        break;
    default:
        echo "{}";
        break;
}