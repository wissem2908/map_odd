<?php


include './config.php';

try {
    // Connection to the database
    $bdd = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

   
    $req = $bdd->prepare('SELECT nomWilaya, ImageProfil FROM `wilayas` left join villes on wilayas.idWilaya = villes.idWilaya left join descriptions on villes.idDescription = descriptions.idDescription where nomWilaya = ?');
 $res = $req->execute([$_POST['name']]); // ✅ wrap in array


 
   $res = $req->fetch(PDO::FETCH_ASSOC);
    echo json_encode($res);
 



} catch (Exception $e) {
    $msg = $e->getMessage();
    echo $msg;
}