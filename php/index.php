<!-- ./php/index.php -->

<blockquote>
  <br>
  <p>PARAMS URL :</p>
  <ul>
    <li>no_polygon, <small>disable all params and show all scans</small></li>
    <li>insert_scans = 0 > int > 100000</li>
    <li>scans_legit = 0|1</li>
    <li>scans_company = upsa|biogaran|meditect</li>
    <li>scans_type = pharmacy|patient|operator</li>
    <li>scans_item = efferalgan|doliprane</li>
  </ul>
  <hr>
  <b>log:</b>
  <br>

  <?php

  error_reporting(-1);
  ini_set('display_errors', 'On');

  try {

    // CONF
    $host = "database";
    $port = 3306;
    $database = "meditect";
    $user = "meditect";
    $pass = "meditect";

    // CONNECT DB
    $db = new PDO("mysql:host=$host;port=$port;dbname=$database", $user, $pass);

    // // MODEL
    $model = $db->prepare("CREATE TABLE scans (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    point POINT,
    userId INT,
    companyId INT,
    itemId INT,
    createdAt DATE,
    legit INT
  )");
    $model->execute();

    $company = $db->prepare("CREATE TABLE company (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    name VARCHAR( 10 )
  )");
    $company->execute();

    $item = $db->prepare("CREATE TABLE item (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    name VARCHAR( 10 )
  )");
    $item->execute();

    $users = $db->prepare("CREATE TABLE users (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    name VARCHAR( 10 ),
    type VARCHAR( 10 )
  )");
    $users->execute();

    // // WRITE
    if (isset($_GET["insert_scans"]) && intval($_GET["insert_scans"])) {
      echo date(DATE_ATOM, time()) . " > insert scans : " . $_GET['insert_scans'] . "<br>";
      $start = strtotime("10 September 2000");
      $end = strtotime("22 July 2010");
      $write = $db->prepare("INSERT INTO scans (point, userId, companyId, itemId, createdAt, legit) VALUES (POINT(:lat, :lng), :userId, :companyId, :itemId, :createdAt, :legit)");
      $write->bindParam(":lat", $lat);
      $write->bindParam(":lng", $lng);
      $write->bindParam(":userId", $userId);
      $write->bindParam(":companyId", $companyId);
      $write->bindParam(":itemId", $itemId);
      $write->bindParam(":legit", $legit);
      $write->bindParam(":createdAt", $createdAt);
      for ($i = 0; $i < max(0, min(100000, intval($_GET["insert_scans"]))); $i++) {
        $lat = 45 + round(-5 * 1000 + rand(0, 10 * 1000)) / 1000;
        $lng = 0 + round(-5 * 1000 + rand(0, 10 * 1000)) / 1000;
        $userId = rand(1, 3);
        $companyId = rand(1, 3);
        $itemId = rand(1, 3);
        $legit = rand(0, 1);
        $timestamp = mt_rand($start, $end);
        $createdAt = date("Y-m-d", $timestamp);
        $write->execute();
      }
      echo date(DATE_ATOM, time()) . " > insert scans : DONE <br>";
    }

    // READ
    // $read = $db->prepare("SELECT ST_AsGeojson(point) AS geojson, ST_Distance(point, POINT(0, 0)) AS dist_meter from scans");
    if (!isset($_GET["no_polygon"])) {
      $read = $db->prepare("
        SELECT
        ST_AsGeojson(point) AS geojson,
        ROUND(ST_Distance_Sphere(point, POINT(45, 0))) / 1000 AS dist
        FROM scans
        LEFT JOIN company ON scans.companyId = company.id
        LEFT JOIN users ON scans.userId = users.id
        LEFT JOIN item ON scans.itemId = item.id
        WHERE ST_CONTAINS(ST_GEOMFROMTEXT('POLYGON((44 -1, 44 1, 46 1, 44 -1))'), point)
          AND scans.legit = :legit
          AND company.name = :company_name
          AND users.type = :user_type
          AND item.name = :item_name
          AND scans.createdAt BETWEEN :from AND :to
        ORDER BY dist
        LIMIT 100000
      ");
    } else {
      $read = $db->prepare("
        SELECT
        ST_AsGeojson(point) AS geojson
        FROM scans
        LIMIT 100000
      ");
    }
    // $read = $db->prepare("SELECT ST_AsGeojson(point) AS geojson, ROUND(ST_Distance_Sphere(point, POINT(45, 0))) / 1000 AS dist FROM scans WHERE ST_CONTAINS(ST_GEOMFROMTEXT('POLYGON((44 -1, 44 1, 46 1, 44 -1))'), point) ORDER BY dist");
    // $read = $db->prepare("SELECT ST_AsGeoJSON(point) as geojson from scans");
    $legit = isset($_GET["scans_legit"]) ? $_GET["scans_legit"] : true;
    $company_name = isset($_GET["scans_company"]) ? $_GET["scans_company"] : "upsa";
    $user_type = isset($_GET["scans_type"]) ? $_GET["scans_type"] : "patient";
    $item_name = isset($_GET["scans_item"]) ? $_GET["scans_item"] : "doliprane";
    $from = "2005-04-20";
    $to = "2015-04-20";
    $read->bindParam(":legit", $legit);
    $read->bindParam(":company_name", $company_name);
    $read->bindParam(":user_type", $user_type);
    $read->bindParam(":item_name", $item_name);
    $read->bindParam(":from", $from);
    $read->bindParam(":to", $to);
    $read->execute();
    $rows = $read->fetchAll(PDO::FETCH_OBJ);
    // echo json_encode($rows);
  ?>
</blockquote>

<style>
  body {
    margin: 0
  }

  #mapid {
    height: 50vh
  }
</style>
<!-- leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin=""></script>
<!-- clusters -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" crossorigin="" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" crossorigin="" />
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js" crossorigin=""></script>
<div id="mapid"></div>
<script>
  var markers = <?php echo json_encode($rows); ?>;
  var mymap = L.map('mapid').setView([45, 0], 6);
  L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
    maxZoom: 18,
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
      '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
      'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
    id: 'mapbox/streets-v11',
    tileSize: 512,
    zoomOffset: -1
  }).addTo(mymap);

  var polygon = L.polygon([
    [44, -1],
    [44, 1],
    [46, 1]
  ], {
    color: 'red',
    stroke: 0
  }).addTo(mymap);

  var markerClusterGroup = L.markerClusterGroup();
  markers.forEach(marker => {
    // L.marker(JSON.parse(marker.geojson).coordinates).addTo(mymap)
    markerClusterGroup.addLayer(L.marker(JSON.parse(marker.geojson).coordinates));
  })
  mymap.addLayer(markerClusterGroup);
</script>
<?php

    // // DROP
    // $drop = $db->prepare("DROP TABLE scans");
    // $drop->execute();

    // CLEAR
    $db = null;
  } catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
  }
?>