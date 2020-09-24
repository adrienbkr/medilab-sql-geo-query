<?php

try {
  // set header 
  header("Access-Control-Allow-Origin: *");
  // check param
  if (!isset($_GET['search']))
    throw new Exception('param search is missing.');
  // connect db with pdo -> $db
  require_once("../db.php");
  // build query
  $search = $db->prepare("
    SELECT
      pharmacies.gid as id,
      pharmacies.potentiel as pot,
      pharmacies.name as name,
      pharmacies.updated_at as updated_at,
      geographies.name as section,
      ST_ASGEOJSON(pharmacies.geog) as geojson
    FROM pharmacies
    INNER JOIN geographies ON ST_COVERS(geographies.geog, pharmacies.geog)
      AND geographies.type = 'section'
    WHERE LOWER(pharmacies.name) LIKE LOWER('%{$_GET['search']}%')
      OR LOWER(geographies.name) LIKE LOWER('%{$_GET['search']}%')
    ORDER BY section, name
  ");

  // exec query
  $search->execute();
  // fetch all rows
  $rows = $search->fetchAll(PDO::FETCH_OBJ);
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
