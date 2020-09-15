<?php

// convert pharmacies.csv to json
$rowPh = 0;
$rowsPh = [];
$jsonPh = [];
if (($handlePh = fopen("export json/pharmacies.csv", "r")) !== FALSE) {
  while (($dataPh = fgetcsv($handlePh, 9999, ",")) !== FALSE) {
    $num = count($dataPh);
    // echo "<p> $num champs à la ligne $row: <br /></p>\n";
    $rowsPh[$rowPh] = $dataPh;
    $jsonPh[$rowPh] = [];
    for ($c = 0; $c < $num; $c++) {
      $jsonPh[$rowPh][$rowsPh[0][$c]] = $dataPh[$c];
    }
    $rowPh++;
  }
  fclose($handlePh);
  // echo json_encode($jsonPh);
}

// convert annuaire.csv to json
$rowAnn = 0;
$rowsAnn = [];
$jsonAnn = [];
if (($handleAnn = fopen("export json/annuaire.csv", "r")) !== FALSE) {
  while (($dataAnn = fgetcsv($handleAnn, 9999, ",")) !== FALSE) {
    $num = count($dataAnn);
    // echo "<p> $num champs à la ligne $row: <br /></p>\n";
    $rowsAnn[$rowAnn] = $dataAnn;
    $jsonAnn[$rowAnn] = [];
    for ($c = 0; $c < $num; $c++) {
      $jsonAnn[$rowAnn][$rowsAnn[0][$c]] = $dataAnn[$c];
    }
    $rowAnn++;
  }
  fclose($handleAnn);
  // echo json_encode($jsonAnn);
}

$counter = 0;
$counter2 = 0;
foreach ($jsonAnn as $keyAnn => $rAnn) {
  foreach ($jsonPh as $keyPh => $rPh) {
    $counter2++;
    if($rAnn["Pharmacie"] != "Pharmacie" && str_replace("pharmacie ", "", strtolower($rPh["name"])) == strtolower($rAnn["Pharmacie"])) {
      // echo $rPh["name"] . " - " . $rAnn["Pharmacie"] . "\n";
      $jsonPh[$keyPh]["annuaire"] = $rAnn;
      $counter++;
    }
  }
}
// echo "match : " . $counter . " / ". count($jsonPh). " / ". count($jsonAnn);
echo json_encode($jsonPh);
