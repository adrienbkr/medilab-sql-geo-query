<?php

try {

  // connect db with pdo
  require_once("./db.php");

  // if(isset($_GET['insert_scans'])) goto insert_scans;

  // DROP
  $db->exec("DROP MATERIALIZED VIEW metrics");
  $db->exec("ALTER TABLE pharmacies DROP CONSTRAINT fk_pharmacy_user");
  $db->exec("ALTER TABLE products DROP CONSTRAINT fk_company");
  $db->exec("ALTER TABLE items DROP CONSTRAINT fk_product");
  $db->exec("ALTER TABLE scans DROP CONSTRAINT fk_user");
  $db->exec("ALTER TABLE scans DROP CONSTRAINT fk_item");
  $db->exec("DROP TABLE pharmacies");
  $db->exec("DROP TABLE companies");
  $db->exec("DROP TABLE products");
  $db->exec("DROP TABLE items");
  $db->exec("DROP TABLE scans");
  $db->exec("DROP TABLE geographies");
  $db->exec("DROP TABLE users");

  // return;

  // ENUMS
  $db->exec("
    CREATE TYPE geography_types AS ENUM (
      'town',
      'region',
      'section',
      'country'
    )");

  // $db->exec("
  //   CREATE TYPE scan_status AS ENUM (
  //     'legit',
  //     'warning',
  //     'fraud'
  //   )");

  // $db->exec("
  //   CREATE TYPE roles AS ENUM (
  //     'patient',
  //     'pharmacy',
  //     'operator'
  //   )");

  // TABLES
  // $db->exec("
  //   CREATE TABLE users (
  //     id serial PRIMARY KEY,
  //     name varchar,
  //     role roles
  //   )");

  $db->exec("
    CREATE TABLE pharmacies (
      gid serial PRIMARY KEY,
      name varchar,
      geog geography(POINT),
      updated_at timestamp NULL,
      turnover_daily numeric NULL,
      frequency_daily numeric NULL,
      cart_avg numeric NULL,
      surface numeric NULL,
      employee numeric NULL,
      showcase numeric NULL,
      counters numeric NULL,
      environment varchar NULL
    )");

  // $db->exec("
  //   CREATE TABLE companies (
  //     id serial PRIMARY KEY,
  //     name varchar
  //   )");

  // $db->exec("
  //     CREATE TABLE products (
  //       id serial PRIMARY KEY,
  //       name varchar,
  //       company_id INT NOT NULL,
  //       CONSTRAINT fk_company
  //         FOREIGN KEY(company_id) 
  //           REFERENCES companies(id)
  //     )");

  // $db->exec("
  //   CREATE TABLE items (
  //     id serial PRIMARY KEY,
  //     product_id INT NOT NULL,
  //     CONSTRAINT fk_product
  //         FOREIGN KEY(product_id) 
  //           REFERENCES products(id)
  //   )");

  // $db->exec("
  //   CREATE TABLE scans (
  //     gid serial PRIMARY KEY,
  //     created_at TIMESTAMP,
  //     status scan_status,
  //     user_id INT NOT NULL,
  //     item_id INT NOT NULL,
  //     geog geography(POINT),
  //     CONSTRAINT fk_user
  //         FOREIGN KEY(user_id) 
  //           REFERENCES users(id),
  //     CONSTRAINT fk_item
  //       FOREIGN KEY(item_id) 
  //         REFERENCES items(id)
  //   )");

  $db->exec("
    CREATE TABLE geographies (
      gid serial PRIMARY KEY,
      name varchar,
      type geography_types,
      geography_id INT,
      geog geography(POLYGON)
    )");


  // DATA

  // geographies
  require_once('./datas.php');
  // ST_PolyFromText
  foreach ($geographies as $geography) {
    $db->exec("INSERT INTO geographies (name, type, geog) VALUES ({$geography['name']}, {$geography['type']}, ST_PolyFromText({$geography['polygon']}))");
    // echo ("INSERT INTO geographies (name, type, geog) VALUES ({$geography['name']}, {$geography['type']}, ST_PolyFromText({$geography['polygon']}))");
    // echo "<br>";
  }

  // companies
  // $companies = [
  //   ["name" => "'upsa'"],
  //   ["name" => "'biogaran'"],
  //   ["name" => "'meditect'"]
  // ];

  // foreach ($companies as $company) {
  //   $db->exec("INSERT INTO companies (name) VALUES ({$company["name"]})");
  //   // echo ("INSERT INTO companies (name) VALUES ({$company["name"]})");
  //   // echo "<br>";
  // }

  // products
  // $products = [
  //   [
  //     "name" => "'efferalgan 500mg'",
  //     "company_id" => "1"
  //   ],
  //   [
  //     "name" => "'dolipran 1g'",
  //     "company_id" => "2"
  //   ],
  //   [
  //     "name" => "'meditex 30mg'",
  //     "company_id" => "3"
  //   ]
  // ];

  // foreach ($products as $product) {
  //   $db->exec("INSERT INTO products (name, company_id) VALUES ({$product["name"]}, {$product["company_id"]})");
  //   // echo ("INSERT INTO products (name, company_id) VALUES ({$product["name"]}, {$product["company_id"]})");
  //   // echo "<br>";
  // }

  // users
  // $users = [
  //   [
  //     "id" => "1",
  //     "name" => "'Dr Michel'",
  //     "role" => "'pharmacy'"
  //   ],
  //   [
  //     "id" => "2",
  //     "name" => "'Dr Jean'",
  //     "role" => "'pharmacy'"
  //   ],
  //   [
  //     "id" => "3",
  //     "name" => "'Dr Robert'",
  //     "role" => "'pharmacy'"
  //   ],
  //   [
  //     "id" => "4",
  //     "name" => "'John'",
  //     "role" => "'operator'"
  //   ],
  //   [
  //     "id" => "5",
  //     "name" => "'Laura'",
  //     "role" => "'operator'"
  //   ],
  //   [
  //     "id" => "6",
  //     "name" => "'+225 11 22 33'",
  //     "role" => "'patient'"
  //   ],
  //   [
  //     "id" => "7",
  //     "name" => "'+225 22 33 44'",
  //     "role" => "'patient'"
  //   ],
  //   [
  //     "id" => "8",
  //     "name" => "'+225 33 44 55'",
  //     "role" => "'patient'"
  //   ],
  //   [
  //     "id" => "9",
  //     "name" => "'+225 44 55 66'",
  //     "role" => "'patient'"
  //   ],
  //   [
  //     "id" => "10",
  //     "name" => "'+225 55 66 77'",
  //     "role" => "'patient'"
  //   ],
  //   [
  //     "id" => "11",
  //     "name" => "'+225 66 77 88'",
  //     "role" => "'patient'"
  //   ],
  //   [
  //     "id" => "12",
  //     "name" => "'+225 77 88 99'",
  //     "role" => "'patient'"
  //   ],
  //   [
  //     "id" => "13",
  //     "name" => "'+225 88 99 00'",
  //     "role" => "'patient'"
  //   ]
  // ];

  // foreach ($users as $user) {
  //   $db->exec("INSERT INTO users (id, name, role) VALUES ({$user["id"]}, {$user["name"]}, {$user["role"]})");
  //   // echo ("INSERT INTO users (id, name, role) VALUES ({$user["id"]}, {$user["name"]}, {$user["role"]})");
  //   // echo "<br>";
  // }

  // pharmacies
  $json = file_get_contents("./export/phs.json");
  $pharmacies = json_decode($json);
  foreach ($pharmacies as $key => $pharma) {
    $pharmacies[$key]->name = "'{$pharmacies[$key]->name}'";
    $pharmacies[$key]->geog = "'POINT(" . $pharma->location->latitude . " " . $pharma->location->longitude . ")'";
    $pharmacies[$key]->updated_at = "NULL";
    $pharmacies[$key]->turnover_daily = "NULL";
    $pharmacies[$key]->frequency_daily = "NULL";
    $pharmacies[$key]->cart_avg = "NULL";
    $pharmacies[$key]->surface = "NULL";
    $pharmacies[$key]->employee = "NULL";
    $pharmacies[$key]->showcase = "NULL";
    $pharmacies[$key]->counters = "NULL";
    $pharmacies[$key]->environment = "NULL";
  }

  // print_r($pharmacies);

  $jsonAnn = file_get_contents("./export/annuaire.json");
  $annuaire = json_decode($jsonAnn);
  foreach ($pharmacies as $key => $pharma) {
    foreach ($annuaire as $keyAnn => $ann) {
      if (strpos($pharma->name, $ann->Pharmacie)) {
        $pharmacies[$key]->updated_at = "'".date('c', strtotime($ann->updated_at))."'";
        $pharmacies[$key]->turnover_daily = $ann->turnover_daily != "" ? $ann->turnover_daily : "NULL";
        $pharmacies[$key]->frequency_daily = $ann->frequency_daily != "" ? $ann->frequency_daily : "NULL";
        $pharmacies[$key]->cart_avg = $ann->cart_avg != "" ? $ann->cart_avg : "NULL";
        $pharmacies[$key]->surface = $ann->surface != "" ? $ann->surface : "NULL";
        $pharmacies[$key]->employee = $ann->employee != "" ? $ann->employee : "NULL";
        $pharmacies[$key]->showcase = $ann->showcase != "" ? $ann->showcase : "NULL";
        $pharmacies[$key]->counters = $ann->counters != "" ? $ann->counters : "NULL";
        $pharmacies[$key]->environment = $ann->environment != "" ? "'".$ann->environment."'"  : "NULL";
        // echo "<pre>";
        // print_r($pharmacies[$key]);
        // echo "</pre>";
        // echo "<br>";
        break;
      }
    }
  }

  // $pharmacies = [
  //   [
  //     "name" => "'pharmacie des lagunes'",
  //     "geog" => "'POINT(-3.994655 5.307452)'"
  //   ]
  // ];
  // echo count($pharmacies);
  $ci = 0;
  foreach ($pharmacies as $pharmacy) {
    $db->exec("
      INSERT INTO pharmacies (
        name,
        geog,
        updated_at,
        turnover_daily,
        frequency_daily,
        cart_avg,
        surface,
        employee,
        showcase,
        counters,
        environment
      ) VALUES (
        {$pharmacy->name},
        ST_PointFromText({$pharmacy->geog}),
        {$pharmacy->updated_at},
        {$pharmacy->turnover_daily},
        {$pharmacy->frequency_daily},
        {$pharmacy->cart_avg},
        {$pharmacy->surface},
        {$pharmacy->employee},
        {$pharmacy->showcase},
        {$pharmacy->counters},
        {$pharmacy->environment}
      )");
    // echo "<pre>";
    // print_r($pharmacy);
    // echo "</pre>";
    // echo "<br>";
    $ci++;
  }
  echo $ci . " imported";

  // items
  // for ($i = 0; $i < 10; $i++) {
  //   $id = rand(1, count($products));
  //   $db->exec("INSERT INTO items (product_id) VALUES ($id)");
  //   // echo ("INSERT INTO items (product_id) VALUES ($id)");
  //   // echo "<br>";
  // }


  // scans
  // insert_scans:
  // for ($i = 0; $i < 100000; $i++) {
  //   $offsetLat = 7;
  //   $deltaLat = 8;
  //   $offsetLng = -5;
  //   $deltaLng = 8;
  //   $lat =  $offsetLat + round(- ($deltaLat / 2) * 1000 + rand(0, $deltaLat * 1000)) / 1000;
  //   $lng =  $offsetLng + round(- ($deltaLng / 2) * 1000 + rand(0, $deltaLng * 1000)) / 1000;
  //   $geog = "'POINT($lat $lng)'";
  //   $status = (["'legit'", "'legit'", "'legit'", "'legit'", "'warning'", "'warning'", "'fraud'"])[rand(0, 6)];
  //   $user_id = rand(1, 13);
  //   $item_id = rand(1, 10);
  //   $created_at = date('\'c\'', rand(1514764800, 1609459200));
  //   $db->exec("INSERT INTO scans (created_at, status, user_id, item_id, geog) VALUES ($created_at, $status, $user_id, $item_id, ST_PointFromText($geog))");
  //   // echo ("INSERT INTO scans (created_at, status, user_id, item_id, geog) VALUES ($created_at, $status, $user_id, $item_id, ST_PointFromText($geog))");
  //   // echo "<br>";
  // }

  // INDEX
  // $db->exec("CREATE INDEX companies_name ON companies (name)");
  // $db->exec("CREATE INDEX companies_id ON companies (id)");
  // $db->exec("CREATE INDEX users_id ON users (id)");
  // $db->exec("CREATE INDEX users_role ON users (role)");
  // $db->exec("CREATE INDEX items_product_id ON items (product_id)");
  // $db->exec("CREATE INDEX products_name_company_id ON products (name, company_id)");
  $db->exec("CREATE INDEX pharmacies_gid ON pharmacies (gid)");
  $db->exec("CREATE INDEX pharmacies_geog_gist ON pharmacies USING GIST (geog)");
  // $db->exec("CREATE INDEX scans_geog_gist ON scans USING GIST (geog)");
  // $db->exec("CREATE INDEX scans_status_user_id_item_id ON scans (status, user_id, item_id)");
  $db->exec("CREATE INDEX geographies_geog_gist ON geographies USING GIST (geog)");

  // METRICS
  // $db->exec("
  //   CREATE MATERIALIZED VIEW metrics AS
  //   SELECT
  //     count(scans.gid) as sum,
  //     geographies.name as geography,
  //     geographies.type as geography_type,
  //     companies.name as company,
  //     products.name as product,
  //     scans.status as status,
  //     users.role as role,
  //     extract(YEAR FROM scans.created_at) as year,
  //     extract(MONTH FROM scans.created_at) as month,
  //     extract(DAY FROM scans.created_at) as day
  //   FROM scans
  //   JOIN geographies ON ST_COVERS(geographies.geog, scans.geog)
  //   JOIN users ON users.id = scans.user_id
  //   JOIN items ON items.id = scans.item_id
  //   JOIN products ON products.id = items.product_id
  //   JOIN companies ON companies.id = products.company_id
  //   GROUP BY
  //     geography,
  //     geography_type,
  //     company,
  //     product,
  //     status,
  //     role,
  //     year,
  //     month,
  //     day
  // ");

} catch (PDOException $e) {
  print "Erreur !: " . $e->getMessage() . "<br/>";
  die();
}
