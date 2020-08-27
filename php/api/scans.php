<?php

try {

  // connect db with pdo
  require_once("../db.php");

  $read = $db->prepare("
    SELECT 
      created_at,
      status,
      users.role as user_role,
      users.name as user_name,
      pharmacies.name as pharmacy,
      ST_ASGEOJSON(geog) as geojson
    FROM scans
    JOIN users ON users.id = scans.user_id
    LIMIT 100
  ");

  $read->execute();
  $rows = $read->fetchAll(PDO::FETCH_OBJ);

  echo json_encode($rows);

} catch (PDOException $e) {
  print "Erreur !: " . $e->getMessage() . "<br/>";
  die();
}
