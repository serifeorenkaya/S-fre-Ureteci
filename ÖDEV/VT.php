<?php
$server = "localhost";
$dbname = "sifreureteci";
$user = "root";
$pass = "";

try {
    $baglanti = new PDO("mysql:host=$server;dbname=$dbname", $user, $pass);
    $baglanti->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   
} catch (PDOException $pe) {
    echo "Bağlantı Hatası: " . $pe->getMessage();
}
?>