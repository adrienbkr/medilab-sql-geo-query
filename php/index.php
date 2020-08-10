<!-- ./php/index.php -->
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

  // $company = $db->prepare("CREATE TABLE company (
  //   id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  //   name VARCHAR( 10 )
  // )");
  // $company->execute();

  // $item = $db->prepare("CREATE TABLE item (
  //   id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  //   name VARCHAR( 10 )
  // )");
  // $item->execute();

  // $users = $db->prepare("CREATE TABLE users (
  //   id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  //   name VARCHAR( 10 ),
  //   type VARCHAR( 10 )
  // )");
  // $users->execute();


  // // WRITE
  // $start = strtotime("10 September 2000");
  // $end = strtotime("22 July 2010");
  // $write = $db->prepare("INSERT INTO scans (point, userId, companyId, itemId, createdAt, legit) VALUES (POINT(:lat, :lng), :userId, :companyId, :itemId, :createdAt, :legit)");
  // $write->bindParam(":lat", $lat);
  // $write->bindParam(":lng", $lng);
  // $write->bindParam(":userId", $userId);
  // $write->bindParam(":companyId", $companyId);
  // $write->bindParam(":itemId", $itemId);
  // $write->bindParam(":legit", $legit);
  // $write->bindParam(":createdAt", $createdAt);
  // for ($i = 0; $i < 50000; $i++) {
  //   $lat = 44 + round(-5 * 1000 + rand(0, 10 * 1000)) / 1000;
  //   $lng = 0 + round(-5 * 1000 + rand(0, 10 * 1000)) / 1000;
  //   $userId = rand(1, 2);
  //   $companyId = rand(1, 2);
  //   $itemId = rand(1,3);
  //   $legit = rand(0,1);
  //   $timestamp = mt_rand($start, $end);
  //   $createdAt = date("Y-m-d", $timestamp);
  //   $write->execute();
  // }
  // $name = "Biogaran";
  // $type = "user";
  // $write = $db->prepare("INSERT INTO company (name) VALUES (:name)");
  // $write->bindParam(":name", $name);
  // $write->bindParam(":type", $type);
  // for ($i = 0; $i < 50000; $i++) {
  //   $lat = 44 + round(-5 * 1000 + rand(0, 10 * 1000)) / 1000;
  //   $lng = 0 + round(-5 * 1000 + rand(0, 10 * 1000)) / 1000;
    // $write->execute();
  // }

  // READ
  // $read = $db->prepare("SELECT ST_AsGeojson(point) AS geojson, ST_Distance(point, POINT(0, 0)) AS dist_meter from scans");
  $read = $db->prepare("
    SELECT
    ST_AsGeojson(point) AS geojson,
    ROUND(ST_Distance_Sphere(point, POINT(45, 0))) / 1000 AS dist
    FROM scans
    LEFT JOIN company ON scans.companyId = company.id
    LEFT JOIN users ON scans.userId = users.id
    LEFT JOIN item ON scans.itemId = item.id
    WHERE ST_CONTAINS(ST_GEOMFROMTEXT('POLYGON((44 -1, 44 1, 46 1, 44 -1))'), point)
    AND company.name = :company_name
      AND users.type = :user_type
      AND item.name = :item_name
      AND scans.createdAt BETWEEN :from AND :to
    ORDER BY dist");
  // $read = $db->prepare("SELECT ST_AsGeojson(point) AS geojson, ROUND(ST_Distance_Sphere(point, POINT(45, 0))) / 1000 AS dist FROM scans WHERE ST_CONTAINS(ST_GEOMFROMTEXT('POLYGON((44 -1, 44 1, 46 1, 44 -1))'), point) ORDER BY dist");
  // $read = $db->prepare("SELECT ST_AsGeoJSON(point) as geojson from scans");
  $company_name = "UPSA";
  $user_type = "user";
  $item_name = "Efferalgan";
  $from = "2005-04-20";
  $to = "2010-04-20";
   if (isset($_GET["company_name"]))
    $company_name = $_GET["company_name"];
  if (isset($_GET["user_type"]))
    $user_type = $_GET["user_type"];
  if (isset($_GET["item_name"]))
    $item_name = $_GET["item_name"];
  if (isset($_GET["from"]))
    $from = $_GET["from"];
  if (isset($_GET["to"]))
    $to = $_GET["to"];
  $read->bindParam(":company_name", $company_name);
  $read->bindParam(":user_type", $user_type);
  $read->bindParam(":item_name", $item_name);
  $read->bindParam(":from", $from);
  $read->bindParam(":to", $to);
  $read->execute();
  $rows = $read->fetchAll(PDO::FETCH_OBJ);
  // echo json_encode($rows);
?>
  <style>
    body {
      margin: 0
    }

    #mapid {
      height: 50vh
    }
  </style>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin="" />
  <script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin=""></script>
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
    ]).addTo(mymap);

    var markers = L.markerClusterGroup();
    markers.addLayer(L.marker(getRandomLatLng(mymap)));
    mymap.addLayer(markers);


    markers.forEach(marker => {
      L.marker(JSON.parse(marker.geojson).coordinates).addTo(mymap)
    })
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
