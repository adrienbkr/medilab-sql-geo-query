<?php

use function PHPSTORM_META\type;

try {
  // set header 
  header("Access-Control-Allow-Origin: *");
  
  // if export csv
  if (isset($_GET['export']))
    header('Content-Disposition: attachment;filename="meditect-annuaire-export.csv";');

  // check param
  if (!isset($_GET['search']) || !isset($_GET['filters']))
    throw new Exception('not allowed');
  // connect db with pdo -> $db
  require_once("../db.php");

  // parse filters
  $filters = json_decode($_GET['filters']);
  $queryStr = "";

  // print_r($filters);

  foreach ($filters as $field => $value) {
    if ($value && $value != "")
      switch ($field) {
        case 'section':
          $queryStr .= "AND geographies.name = '$value' ";
          break;
        case 'counters':
        case 'showcase':
          $queryStr .= "AND $field = $value ";
          break;
        case 'turnover_daily':
        case 'frequency_daily':
        case 'employee':
          $values = explode('-', $value);
          $min = $values[0];
          $max = $values[1];
          if ($max == "max")
            $queryStr .= "AND $field >= $min ";
          else if ($min == "min")
            $queryStr .= "AND $field < $max ";
          else
            $queryStr .= "AND $field BETWEEN $min AND $max ";
          break;
        default:
          break;
      }
  }

  // echo $queryStr;
  // return;

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
    WHERE (LOWER(pharmacies.name) LIKE LOWER('%{$_GET['search']}%') OR LOWER(geographies.name) LIKE LOWER('%{$_GET['search']}%'))
    {$queryStr}
    ORDER BY potentiel
    DESC
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
  // send res json or export csv
  if (!isset($_GET['export']))
    echo json_encode($rows);
  else
    foreach ($rows as $index => $row) {
      $csvHeadArr = [];
      $csvRowArr = [];
      foreach ($row as $key => $value) {
        if ($index == 0) $csvHeadArr[$key] = $key;
        $csvRowArr[$key] = gettype($value) == "object" ? json_encode($value) : $value;
      }

      if ($index == 0) echo implode(",", $csvHeadArr)."\n";
      echo implode(",", $csvRowArr)."\n";
    }
} catch (Exception $e) {
  // status res 500
  http_response_code(500);
  // send error
  echo $e->getMessage();
  die();
}
