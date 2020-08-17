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


  $model = $db->prepare("CREATE TABLE sections (
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    polygon POLYGON,
    name VARCHAR( 10 )
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

  // WRITE
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
      $lat = 9 + round(-2 * 1000 + rand(0, 4 * 1000)) / 1000;
      $lng = -3 + round(-1 * 1000 + rand(0, 2 * 1000)) / 1000;
      $userId = 1;
      // $userId = rand(1, 3);
      $companyId = 1;
      // $companyId = rand(1, 3);
      $itemId = 1;
      // $itemId = rand(1, 3);
      $legit = 1;
      // $legit = rand(0, 1);
      $timestamp = mt_rand($start, $end);
      $createdAt = date("Y-m-d", $timestamp);
      $write->execute();
    }
    echo date(DATE_ATOM, time()) . " > insert scans : DONE <br>";
    return;
  }

  // READ

  $readSections = $db->prepare("
    SELECT 
      name, 
      ST_AsGeojson(polygon) AS geojson,
      ST_AsText(polygon) AS polygon
    FROM sections
  ");
  $readSections->execute();
  $rowsSections = $readSections->fetchAll(PDO::FETCH_OBJ);

  switch ($_GET["route"]) {

    case "sections":
      $metric = isset($_GET["metric"]) ? $_GET["metric"] : "scans.legit";
      $readMetrics = $db->prepare("
        SELECT
          count(scans.id) as sum,
          ". $metric ." as value
        FROM scans
        LEFT JOIN company ON scans.companyId = company.id
        LEFT JOIN users ON scans.userId = users.id
        LEFT JOIN item ON scans.itemId = item.id
        WHERE ST_CONTAINS(ST_GEOMFROMTEXT('" . $rowsSections[1]->polygon . "'), point)
        " . (isset($_GET["legit"]) && $_GET["legit"] != "" ? '' : '# ') . " AND scans.legit = :legit
        " . (isset($_GET["company_name"]) && $_GET["company_name"] != "" ? '' : '# ') . " AND company.name = :company_name
        " . (isset($_GET["user_type"]) && $_GET["user_type"] != "" ? '' : '# ') . " AND users.type = :user_type
        " . (isset($_GET["item_name"]) && $_GET["item_name"] != "" ? '' : '# ') . " AND item.name = :item_name
        # AND scans.createdAt BETWEEN :from AND :to
        GROUP BY ". $metric ."
      ");

      // DEBUG
      // print_r($readMetrics);
      // return;

      $legit = isset($_GET["legit"]) ? $_GET["legit"] : true;
      $company_name = isset($_GET["company_name"]) ? $_GET["company_name"] : "upsa";
      $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : "patient";
      $item_name = isset($_GET["item_name"]) ? $_GET["item_name"] : "doliprane";
      $from = isset($_GET["from"]) ? $_GET["from"] : "2005-04-20";
      $to = isset($_GET["to"]) ? $_GET["to"] : "2015-04-20";

      $readMetrics->bindParam(":legit", $legit);
      $readMetrics->bindParam(":company_name", $company_name);
      $readMetrics->bindParam(":user_type", $user_type);
      $readMetrics->bindParam(":item_name", $item_name);
      $readMetrics->bindParam(":from", $from);
      $readMetrics->bindParam(":to", $to);

      $readMetrics->execute();
      $rowsMetrics = $readMetrics->fetchAll(PDO::FETCH_OBJ);
      
      echo json_encode([
        "metrics" => isset($rowsMetrics) ? $rowsMetrics : [],
        "sections" => isset($rowsSections) ? $rowsSections : []
      ]);
      break;

    case "scans":
      // $read = $db->prepare("SELECT ST_AsGeojson(point) AS geojson, ST_Distance(point, POINT(0, 0)) AS dist_meter from scans");
      if (isset($_GET["no_polygon"]) && $_GET["no_polygon"] == "false") {
        $read = $db->prepare("
        SELECT
          createdAt,
          legit,
          users.name AS user_name,
          users.type AS user_type,
          company.name AS company_name,
          item.name AS item_name,
          ST_AsGeojson(point) AS geojson
        FROM scans
        LEFT JOIN company ON scans.companyId = company.id
        LEFT JOIN users ON scans.userId = users.id
        LEFT JOIN item ON scans.itemId = item.id
        WHERE ST_CONTAINS(ST_GEOMFROMTEXT('" . $rowsSections[1]->polygon . "'), point)
          " . (isset($_GET["legit"]) && $_GET["legit"] != "" ? '' : '# ') . " AND scans.legit = :legit
          " . (isset($_GET["company_name"]) && $_GET["company_name"] != "" ? '' : '# ') . " AND company.name = :company_name
          " . (isset($_GET["user_type"]) && $_GET["user_type"] != "" ? '' : '# ') . " AND users.type = :user_type
          " . (isset($_GET["item_name"]) && $_GET["item_name"] != "" ? '' : '# ') . " AND item.name = :item_name
          # AND scans.createdAt BETWEEN :from AND :to
        LIMIT 25000
      ");
      } else {
        $read = $db->prepare("
          SELECT
            createdAt,
            legit,
            users.name AS user_name,
            users.type AS user_type,
            company.name AS company_name,
            item.name AS item_name,
            ST_AsGeojson(point) AS geojson
          FROM scans
          LEFT JOIN company ON scans.companyId = company.id
          LEFT JOIN users ON scans.userId = users.id
          LEFT JOIN item ON scans.itemId = item.id
          LIMIT 25000
        ");
      }
      // $read = $db->prepare("SELECT ST_AsGeojson(point) AS geojson, ROUND(ST_Distance_Sphere(point, POINT(45, 0))) / 1000 AS dist FROM scans WHERE ST_CONTAINS(ST_GEOMFROMTEXT('POLYGON((44 -1, 44 1, 46 1, 44 -1))'), point) ORDER BY dist");
      // $read = $db->prepare("SELECT ST_AsGeoJSON(point) as geojson from scans");

      $legit = isset($_GET["legit"]) ? $_GET["legit"] : true;
      $company_name = isset($_GET["company_name"]) ? $_GET["company_name"] : "upsa";
      $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : "patient";
      $item_name = isset($_GET["item_name"]) ? $_GET["item_name"] : "doliprane";
      $from = isset($_GET["from"]) ? $_GET["from"] : "2005-04-20";
      $to = isset($_GET["to"]) ? $_GET["to"] : "2015-04-20";

      $read->bindParam(":legit", $legit);
      $read->bindParam(":company_name", $company_name);
      $read->bindParam(":user_type", $user_type);
      $read->bindParam(":item_name", $item_name);
      $read->bindParam(":from", $from);
      $read->bindParam(":to", $to);

      // print_r($polygon);
      // return;

      $read->execute();
      $rowsScans = $read->fetchAll(PDO::FETCH_OBJ);

      // DEBUG
      // print_r($read);
      // return

      echo json_encode([
        "scans" => isset($rowsScans) ? $rowsScans : []
      ]);
      break;
  }
?>
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