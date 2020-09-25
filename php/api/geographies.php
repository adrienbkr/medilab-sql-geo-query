<?php
try {
  // set header 
  header("Access-Control-Allow-Origin: *");
  // connect db with pdo
  require_once("../db.php");
  // build query
  $read = $db->prepare("
    SELECT
    geographies.name,
    ST_ASGEOJSON(geographies.geog) as geojson
    FROM geographies
  ");
  // exec query
  $read->execute();
  // fetch all rows
  $rows = $read->fetchAll(PDO::FETCH_OBJ);
  // convert geojson
  foreach ($rows as $key => $row) {
    $geojson = json_decode($row->geojson);
    // reverse coords
    foreach ($geojson->coordinates as $keyA => $coords) {
      foreach ($coords as $keyB => $coord) {
        $geojson->coordinates[$keyA][$keyB][0] = $coord[1];
        $geojson->coordinates[$keyA][$keyB][1] = $coord[0];
      }
    }
    // reverse geojson
    $rows[$key]->geojson = (Object) [
      "type" => "Feature",
      "properties" => (Object) [],
      "geometry" => $geojson
    ];
  }
  // status res 200
  http_response_code(200);
  // send res
  echo json_encode($rows);
} catch (Exception $e) {
  // status res 500
  http_response_code(500);
  // send error
  echo $e->getMessage();
  die();
}
