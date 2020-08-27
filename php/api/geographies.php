<?php

try {

  // connect db with pdo
  require_once("../db.php");

  $read = $db->prepare("
    SELECT
    geographies.name,
    ST_ASGEOJSON(geographies.geog) as geojson
    FROM geographies
  ");

  $read->execute();
  $rows = $read->fetchAll(PDO::FETCH_OBJ);

  echo json_encode($rows);

} catch (PDOException $e) {
  print "Erreur !: " . $e->getMessage() . "<br/>";
  die();
}
