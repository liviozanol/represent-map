<?php
include_once "header.php";
//LOAD DATA INFILE '/var/www/html/livio/mapa/db/courses.csv' INTO TABLE courses FIELDS TERMINATED BY ',' ENCLOSED BY '"' LINES TERMINATED BY '\r\n' IGNORE 1 ROWS;
// This is used to submit new markers for review.
// Markers won't appear on the map until they are approved.




$owner_name = mysql_real_escape_string(parseInput($_POST['owner_name']));
$owner_email = mysql_real_escape_string(parseInput($_POST['owner_email']));
$title = mysql_real_escape_string(parseInput($_POST['title']));
$type = mysql_real_escape_string(parseInput($_POST['type']));
$inep = mysql_real_escape_string(parseInput($_POST['inep']));
$cnpj = mysql_real_escape_string(parseInput($_POST['cnpj']));
$address_street = mysql_real_escape_string(parseInput($_POST['address_street']));
$address_number = mysql_real_escape_string(parseInput($_POST['address_number']));
$address_neighborhood = mysql_real_escape_string(parseInput($_POST['address_neighborhood']));
$address_city = mysql_real_escape_string(parseInput($_POST['address_city']));
$address_state = mysql_real_escape_string(parseInput($_POST['address_state']));
$address_postal_code = mysql_real_escape_string(parseInput($_POST['address_postal_code']));
$address_telephone = mysql_real_escape_string(parseInput($_POST['address_telephone']));
$lat = mysql_real_escape_string(parseInput($_POST['lat']));
$lng = mysql_real_escape_string(parseInput($_POST['lng']));
$uri = mysql_real_escape_string(parseInput($_POST['uri']));
$description = mysql_real_escape_string(parseInput($_POST['description']));

// validate fields
//TO DO: Ver fields obrigatórios e checar
if(empty($title) || empty($type) || empty($address_street) || empty($inep) || empty($cnpj) || empty($lat) || empty($lng) || empty($owner_name) || empty($owner_email)) {
  echo "Campos obrigatórios não preenchidos";
  exit;
  
} else {
   
   
    // insert into db, wait for approval
    $insert = mysql_query("INSERT INTO places 
    (approved, title, type, inep, cnpj, lat, lng, uri, address_street, address_number, address_neighborhood, address_city, address_state, address_postal_code,  address_telephone, description, owner_name, owner_email) 
    VALUES 
    (null, '$title', '$type', '$inep', '$cnpj', '$lat', '$lng', '$uri', '$address_street', '$address_number', '$address_neighborhood', '$address_city', '$address_state', '$address_postal_code', '$address_telephone', '$description', '$owner_name', '$owner_email')") or die(mysql_error());

    // geocode new submission
    //TO DO: Retirar Geocode
    //$hide_geocode_output = true;
    //include "geocode.php";
    
    echo "success";
    exit;

  
}


?>
