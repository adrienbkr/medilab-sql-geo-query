<?php

try {

  // connect db with pdo
  require_once("../db.php");

  $read = $db->prepare("
    SELECT 
      count(sum),
      year
    FROM metrics
    GROUP BY year
  ");

  $read->execute();
  $rows = $read->fetchAll(PDO::FETCH_OBJ);

  echo json_encode($rows);

} catch (PDOException $e) {
  print "Erreur !: " . $e->getMessage() . "<br/>";
  die();
}
