<?php
$user = "";
$pass = "";

$id="03";
$score="3000";

try {
    $dbh = new PDO('mysql:host=localhost;dbname=data_test', $user, $pass);
    foreach($dbh->query('SELECT * from rank') as $row) {
        print_r($row);
    }
//    $dbh->query('INSERT INTO rank (id, score) values (' . $id . ',' . $score . ')');
    $dbh = null;
} catch (PDOException $e) {
    print "エラー!: " . $e->getMessage() . "<br/>";
    die();
}

