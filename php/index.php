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
  // $model = $db->prepare("CREATE TABLE scans (id INT PRIMARY KEY NOT NULL AUTO_INCREMENT, point POINT)");
  // $model->execute();

  // // WRITE
  // $write = $db->prepare("INSERT INTO scans (point) VALUES (POINT(:lat, :lng))");
  // $write->bindParam(":lat", $lat);
  // $write->bindParam(":lng", $lng);
  // for ($i = 0; $i < 50000; $i++) {
  //   $lat = 44 + round(-5 * 1000 + rand(0, 10 * 1000)) / 1000;
  //   $lng = 0 + round(-5 * 1000 + rand(0, 10 * 1000)) / 1000;
  //   $write->execute();
  // }

  // READ
  // $read = $db->prepare("SELECT ST_AsGeojson(point) AS geojson, ST_Distance(point, POINT(0, 0)) AS dist_meter from scans");
  $read = $db->prepare("SELECT ST_AsGeojson(point) AS geojson, ROUND(ST_Distance_Sphere(point, POINT(45, 0))) / 1000 AS dist FROM scans WHERE ST_CONTAINS(ST_GEOMFROMTEXT('POLYGON((44 -1, 44 1, 46 1, 44 -1))'), point) ORDER BY dist");
  // $read = $db->prepare("SELECT ST_AsGeoJSON(point) as geojson from scans");
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