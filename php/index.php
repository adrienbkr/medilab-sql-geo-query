<?php

try {

  // connect db with pdo
  require_once("./db.php");

  echo  "•••              ••\n";
  echo  " ••              ••\n";
  echo  " ••         ••   ••\n";
  echo  " ••         ••   ••\n";
  echo  " •• ••           ••\n";
  echo  " •••••••    ••     \n";
  echo  " ••   ••    ••   ••\n";
  echo  "•••   •••  •••   ••\n";
  
} catch (PDOException $e) {
  print "Erreur !: " . $e->getMessage() . "<br/>";
  die();
}
