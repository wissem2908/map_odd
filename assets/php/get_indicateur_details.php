<?php


include './config.php';

try {
    // Connection to the database
    $bdd = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

   
    $req = $bdd->prepare('SELECT * FROM `indicateurvilles` left join objectifvilles on indicateurvilles.idObjectifVille = objectifvilles.idObjectifVille left join villes on objectifvilles.idVille = villes.idVille where idIndicateur = ? ');
     $res = $req->execute(array($_POST['id_ind']));


 
 
    $output = [];
    while ($res = $req->fetch(PDO::FETCH_ASSOC)) {
        $output[] = $res;
    }

echo json_encode($output);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);

} catch (Exception $e) {
    $msg = $e->getMessage();
    echo $msg;
}