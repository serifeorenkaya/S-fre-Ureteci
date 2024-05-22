<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #fff0f5;
        }
        .container {
            display: flex;
            justify-content: space-between;
        }
        .tb-color {
            background-color: #ba55d3;
        }
        .bt-color {
            background-color: #ba55d3;
            border-radius: 13px;
        }
        table {
            margin-top: 20px;
            margin-right: 100px;
        }
        .form {
            margin-top: 20px;
            margin-left: 100px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
        }
        .sıfre {
            padding: 20px;
            margin-top: 50px;
            border: 2px solid #ba55d3;
            height: 20px;
            border-radius: 20px;
            background-color: #ba55d3;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="header">ŞİFRE ÜRETECİ</div>
<div class="container">
    <?php
    session_start();
    include "VT.php";
    // Form verilerini tutmak için değişkenler oluşturulur
    $buyukHarflerChecked = isset($_POST['buyukHarfler']) ? 'checked' : '';
    $kucukHarflerChecked = isset($_POST['kucukHarfler']) ? 'checked' : '';
    $ozelKarakterlerChecked = isset($_POST['ozelKarakterler']) ? 'checked' : '';
    $sayiChecked = isset($_POST['sayı']) ? 'checked' : '';
    $yerValue = isset($_POST['yer']) ? htmlspecialchars($_POST['yer']) : '';

    $sifre = isset($_SESSION['sifre']) ? $_SESSION['sifre'] : '';

    // Şifre oluşturma 
    function sifreuret() {
        $buyukHarf = "QWERTYUIOPĞÜASDFGHJKLŞİZXCVBNMÖÇ";
        $kucukHarf = "qwertyuıopğüasdfghjklşizxcvbnmöç";
        $ozelKarakter = "!,;.?*=+%#/&";
        $sayı = "0123456789";

        $karakterler = "";
        $sifre = "";

        if (isset($_POST['buyukHarfler'])) {
            $karakterler .= $buyukHarf;
        }
        if (isset($_POST['kucukHarfler'])) {
            $karakterler .= $kucukHarf;
        }
        if (isset($_POST['ozelKarakterler'])) {
            $karakterler .= $ozelKarakter;
        }
        if (isset($_POST['sayı'])) {
            $karakterler .= $sayı;
        }

        if ($karakterler != "") {
            for ($i = 0; $i < 8; $i++) {
                $randomIndex = rand(0, mb_strlen($karakterler, 'UTF-8') - 1);
                $sifre .= mb_substr($karakterler, $randomIndex, 1, 'UTF-8');
            }
        }
        return $sifre;
    }

    // Form gönderildiğinde işlemleri gerçekleştirme
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['olustur'])) {
            $sifre = sifreuret();
            $_SESSION['sifre'] = $sifre;
        }

        if (isset($_POST['kaydet'])) {
            $sifre = isset($_SESSION['sifre']) ? $_SESSION['sifre'] : sifreuret();
            $yer = isset($_POST['yer']) ? trim($_POST['yer']) : '';

            if (empty($sifre) || empty($yer)) {
                echo "Şifre veya Kullanım Alanı boş olamaz!";
            } else {
                $sorgu = $baglanti->prepare("INSERT INTO sıfreuret(sıfre, kullanımalanı) VALUES(:sifre, :yer)");
                $sorgu->bindParam(':sifre', $sifre);
                $sorgu->bindParam(':yer', $yer);
                $sorgu->execute();
                unset($_SESSION['sifre']); // Şifre kaydedildikten sonra oturumdan kaldırılıyor
            }
        }

        // Silme işlemi
        if (isset($_POST['sil'])) {
            $id = $_POST['sil'];
            $sorgu = $baglanti->prepare("DELETE FROM sıfreuret WHERE id = :id");
            $sorgu->bindParam(':id', $id);
            $sorgu->execute();

            // ID'leri yeniden düzenle
            $baglanti->exec("CREATE TEMPORARY TABLE temp_table AS SELECT sıfre, kullanımalanı FROM sıfreuret");
            $baglanti->exec("DROP TABLE sıfreuret");
            $baglanti->exec("CREATE TABLE sıfreuret (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sıfre VARCHAR(255),
                kullanımalanı VARCHAR(255)
            )");
            $baglanti->exec("INSERT INTO sıfreuret (sıfre, kullanımalanı) SELECT sıfre, kullanımalanı FROM temp_table");
            $baglanti->exec("DROP TEMPORARY TABLE temp_table");
        }
    }
    ?>
    <div>
        <form method="POST" class="form">
            <input type="checkbox" id="buyukHarfler" name="buyukHarfler" <?= $buyukHarflerChecked ?>>Büyük Harf Olsun
            <br>
            <input type="checkbox" id="kucukHarfler" name="kucukHarfler" <?= $kucukHarflerChecked ?>>Küçük Harf Olsun
            <br>
            <input type="checkbox" id="ozelKarakterler" name="ozelKarakterler" <?= $ozelKarakterlerChecked ?>>Özel Karakter Olsun
            <br>
            <input type="checkbox" id="sayı" name="sayı" <?= $sayiChecked ?>>Sayı olsun
            <br>
            Nerede Kullanıcak<input type="text" name="yer" value="<?= $yerValue ?>" />
            <br><br>
            <button class="bt-color" type="submit" name="olustur" value="1">OLUŞTUR</button>
            <button class="bt-color" type="submit" name="kaydet" value="1">KULLANICIYI KAYDET</button>
        </form>
    </div>

    <?php
    echo "<div class='sıfre'>OLUŞTURULAN ŞİFRE: " . (isset($sifre) ? htmlspecialchars((string)$sifre) : "") . "</div>";

    $sorgu = $baglanti->query("SELECT * FROM sıfreuret ORDER BY id ASC");
    echo "<table border='1'>";
    echo "<tr><td class='tb-color'>İd</td><td class='tb-color'>Şifre</td><td class='tb-color'>Kullanım Alanı</td><td class='tb-color'>Sil</td></tr>";
    while ($row = $sorgu->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td>" . htmlspecialchars((string)$row['id']) . "</td><td>" . htmlspecialchars((string)$row['sıfre']) . "</td><td>" . htmlspecialchars((string)$row['kullanımalanı']) . "</td><td><form method='POST' style='display:inline;'><button type='submit' name='sil' value='" . htmlspecialchars((string)$row['id']) . "'>x</button></form></td></tr>";
    }
    echo "</table>";
    ?>
</div>
</body>
</html>
