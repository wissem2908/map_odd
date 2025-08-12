<?php


include './config.php';

try {
    // Connection to the database
    $bdd = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

   
    $req = $bdd->prepare('SELECT * FROM `villes` where LOWER(nomVille) = LOWER(?)');
         $res = $req->execute([$_POST['ville']]);


 
   $res = $req->fetch(PDO::FETCH_ASSOC);
    echo json_encode($res['idVille']);
 



} catch (Exception $e) {
    $msg = $e->getMessage();
    echo $msg;
}