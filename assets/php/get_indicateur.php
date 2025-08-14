<?php


include './config.php';

try {
    // Connection to the database
    $bdd = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

   
    $req = $bdd->prepare('SELECT * FROM `indicateurs` left join cibles on indicateurs.idCible = cibles.idCible where codeIndicateur <> "" and cibles.idObjectif = ? ');
     $res = $req->execute(array($_POST['id_obj']));


 
 
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