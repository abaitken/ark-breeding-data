<?php
header("Content-Type: application/json");

$parts = parse_url($_SERVER['REQUEST_URI']);
parse_str($parts['query'], $query);

$format = 'full';

if(array_key_exists('format', $query))
    $format = $query['format'];

switch ($format) {
    case 'full':
        $mapContents = file_get_contents("../data/maps.json");
        $mapData = json_decode($mapContents, true);
        
        $result = array();
        
        foreach($mapData as $map) {
            $hidden = $map["Hidden"];
            if($hidden) 
                continue;
            
            $id = $map["Id"];
            
            $item = array();
            $item["text"] = $map["Text"];
            $item["filename"] = $id . ".asb";
            $item["url"] = "data/" . $id . ".asb";
            $item["modified"] = date(DATE_ATOM, filemtime("../data/" . $id . ".asb"));
            
            array_push($result, $item);
        }
        
        echo json_encode($result);
        
        break;
    default:
        echo "{}";
        break;
}