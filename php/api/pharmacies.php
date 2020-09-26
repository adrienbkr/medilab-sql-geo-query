<?php
try {
  // set header 
  header("Access-Control-Allow-Origin: *");
  // check param
  if (!isset($_GET['id']))
    throw new Exception('param id is missing.');
  // connect db with pdo -> $db
  require_once("../db.php");
  // build query
  $search = $db->prepare("
    SELECT
      pharmacies.gid as gid,
      pharmacies.name as name,
      pharmacies.updated_at as updated_at,
      pharmacies.turnover_daily as turnover_daily,
      pharmacies.frequency_daily as frequency_daily,
      pharmacies.cart_avg as cart_avg,
      pharmacies.surface as surface,
      pharmacies.employee as employee,
      pharmacies.showcase as showcase,
      pharmacies.counters as counters,
      pharmacies.address as address,
      pharmacies.phone as phone,
      pharmacies.opening as opening,
      pharmacies.photo as photo,
      pharmacies.environment as environment,
      (
        (
          CASE WHEN frequency_daily IS NULL
          THEN 0
          ELSE CEIL((frequency_daily + 1) / 100) END
        ) +
        (
          CASE WHEN cart_avg IS NULL
          THEN 0
          ELSE CEIL((cart_avg + 1) / 1000) END
        ) +
        (
          CASE WHEN employee IS NULL
          THEN 0
          ELSE CEIL((employee + 1) / 5) END
        ) +
        (
          CASE WHEN surface IS NULL
          THEN 0
          ELSE CEIL((surface + 1) / 100) END
        )
      ) as potentiel,
      geographies.name as section,
      ST_ASGEOJSON(pharmacies.geog) as geojson
    FROM pharmacies
    INNER JOIN geographies ON ST_COVERS(geographies.geog, pharmacies.geog)
      AND geographies.type = 'section'
    WHERE pharmacies.gid = {$_GET['id']}
  ");
  // exec query
  $search->execute();
  // fetch all rows
  $rows = $search->fetchAll(PDO::FETCH_OBJ);
  // convert geojson
  foreach ($rows as $key => $row) {
    $rows[$key]->geojson = json_decode($row->geojson);
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
