<?php
include './config.php';

try {
    $bdd = new PDO(
        "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    $req = $bdd->prepare('SELECT * FROM `villes` WHERE LOWER(nomVille) = LOWER(?)');
    $success = $req->execute([$_POST['ville']]);

    if ($success) {
        $row = $req->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo json_encode($row['scoreV']);
        } else {
            echo json_encode(null); // No match found
        }
    } else {
        echo json_encode(null); // Query execution failed
    }

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}