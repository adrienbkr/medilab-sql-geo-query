<?php

error_reporting(-1);
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

} catch (PDOException $e) {
  print "Erreur !: " . $e->getMessage() . "<br/>";
  die();
}