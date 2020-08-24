<?php

error_reporting(1);
ini_set('display_errors', 'On');

try {

  // CONF
  $host = "database";
  $port = 5432;;
  $database = "meditect";
  $user = "meditect";
  $pass = "meditect";

  // CONNECT DB
  $db = new PDO("pgsql:host=$host;port=$port;dbname=$database", $user, $pass);

  // WRITE
  if (isset($_GET["insert_scans"]) && intval($_GET["insert_scans"])) {
    echo date(DATE_ATOM, time()) . " > insert scans : {$_GET["insert_scans"]}<br>";
    $offsetLat = 7;
    $deltaLat = 8;
    $offsetLng = -5;
    $deltaLng = 8;
    for ($i = 0; $i < max(0, min(100000, intval($_GET["insert_scans"]))); $i++) {
      $lat =  $offsetLat + round(- ($deltaLat / 2) * 1000 + rand(0, $deltaLat * 1000)) / 1000;
      $lng =  $offsetLng + round(- ($deltaLng / 2) * 1000 + rand(0, $deltaLng * 1000)) / 1000;
      $geog = "'SRID=4267;POINT($lat $lng)'";
      $status = (['legit', 'unlegit', 'warning'])[rand(0, 2)];
      $userId = rand(1, 6);
      $itemId = rand(1, 28);
      $createdAt = date('c', rand(1514764800, 1609459200));
      $query = "INSERT INTO scans (geog, user_id, item_id, status, created_at) VALUES ($geog, $userId, $itemId, '$status', '$createdAt')";
      // DEBUG
      // print_r("$query  <hr>");
      $write = $db->query($query);
    }
    echo date(DATE_ATOM, time()) . " > insert scans : DONE <br>";
    return;
  }

  if (!isset($_GET["route"])) {
    echo  "•••              ••\n";
    echo  " ••              ••\n";
    echo  " ••         ••   ••\n";
    echo  " ••         ••   ••\n";
    echo  " •• ••           ••\n";
    echo  " •••••••    ••     \n";
    echo  " ••   ••    ••   ••\n";
    echo  "•••   •••  •••   ••\n";
    return;
  }


  // READ
  switch ($_GET["route"]) {

    case "sections":

      $readSections = $db->prepare("
        SELECT
        sections.name,
        ST_ASGEOJSON(sections.geog) as geojson
        FROM sections
      ");

      $readSections->execute();
      $rowsMetrics = $readSections->fetchAll(PDO::FETCH_OBJ);

      echo json_encode([
        "sections" => isset($rowsMetrics) ? $rowsMetrics : []
      ]);

      break;


    case "scans":

      $wheres = [];

      if (
        isset($_GET["neLat"]) && $_GET["neLat"] != ""
        && isset($_GET["neLng"]) && $_GET["neLng"] != ""
        && isset($_GET["swLng"]) && $_GET["swLng"] != ""
        && isset($_GET["swLng"]) && $_GET["swLng"] != ""
      ) {
        $wheres[] = "ST_COVERS(ST_PolyFromText('POLYGON(({$_GET["neLat"]} {$_GET["neLng"]}, {$_GET["swLat"]} {$_GET["neLng"]}, {$_GET["swLat"]} {$_GET["swLng"]}, {$_GET["neLat"]} {$_GET["swLng"]}, {$_GET["neLat"]} {$_GET["neLng"]}))', 4267), scans.geog)";
      }

      if (isset($_GET["status"]) && $_GET["status"] != "") {
        $wheres[] = "scans.status = '{$_GET["status"]}'";
      }

      if (isset($_GET["company_name"]) && $_GET["company_name"] != "") {
        $wheres[] = "companies.name = '{$_GET["company_name"]}'";
      }

      if (isset($_GET["product_name"]) && $_GET["product_name"] != "") {
        $wheres[] = "products.name = '{$_GET["product_name"]}'";
      }

      if (isset($_GET["user_role"]) && $_GET["user_role"] != "") {
        $wheres[] = "users.role = '{$_GET["user_role"]}'";
      }

      $where = count($wheres) > 0 ? "WHERE " . implode(" AND ", $wheres) : "";

      $read = $db->prepare("
        SELECT
          created_at,
          status,
          users.name AS user_name,
          users.role AS user_role,
          companies.name AS company_name,
          products.name AS product_name,
          ST_AsGeojson(geog) AS geojson
        FROM scans
        LEFT JOIN users ON users.id = scans.user_id
        LEFT JOIN items ON items.id = scans.item_id
        LEFT JOIN products ON products.id = items.product_id
        LEFT JOIN companies ON companies.id = products.company_id
        $where
        LIMIT 50000
      ");

      $read->execute();
      $rowsScans = $read->fetchAll(PDO::FETCH_OBJ);

      echo json_encode([
        "scans" => isset($rowsScans) ? $rowsScans : []
      ]);

      break;

    case 'metrics':
      $metrics = $db->prepare("
        SELECT
          sections.name,
          {$_GET["metric"]} as metricType,
          COUNT(scans.gid) as metricValue
        FROM scans
          INNER JOIN sections ON ST_Covers(sections.geog, scans.geog)
          LEFT JOIN users ON users.id = scans.user_id
          LEFT JOIN items ON items.id = scans.item_id
          LEFT JOIN products ON products.id = items.product_id
          LEFT JOIN companies ON companies.id = products.company_id
        GROUP BY sections.name,
          {$_GET["metric"]}
      ");

      $metrics->execute();
      $rowsMetrics = $metrics->fetchAll(PDO::FETCH_OBJ);

      echo json_encode([
        "metrics" => isset($rowsMetrics) ? $rowsMetrics : []
      ]);
      break;
  }
} catch (PDOException $e) {
  print "Erreur !: " . $e->getMessage() . "<br/>";
  die();
}
