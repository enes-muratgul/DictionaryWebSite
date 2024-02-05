<?php
include("baglanti.php");

if (isset($_POST["kaydet"])) {
    $name = $_POST["kullaniciadi"];
    $email = $_POST["email"];
    $password = $_POST["parola"];

    $ekle = "INSERT INTO kullanicilar (kullanici_adi, email, parola) VALUES ('$name', '$email', '$password')";
    $calistirekle = mysqli_query($baglanti, $ekle);

    if ($calistirekle) {
        echo '<div class="alert alert-success" role="alert">
        Kayıt Başarıyla Eklendi.
        </div>';
        
        session_start();
        $_SESSION["user_id"] = mysqli_insert_id($baglanti);
        $_SESSION["user_name"] = $name;
    } else {
        echo '<div class="alert alert-danger" role="alert">
        Kayıt Eklenirken Problem Oluştu.
        </div>';
    }

}

if (isset($_POST["login"])) {
    $loginEmail = $_POST["loginEmail"];
    $loginPassword = $_POST["loginPassword"];

    $sorgula = "SELECT * FROM deneme WHERE email='$loginEmail' AND parola='$loginPassword'";
    $calistir = mysqli_query($baglanti, $sorgula);
    $kullanici = mysqli_fetch_assoc($calistir);

    if ($kullanici) {
        session_start();
        $_SESSION["user_id"] = $kullanici["id"];
        $_SESSION["user_name"] = $kullanici["kullanici_adi"];

        echo '<div class="alert alert-success" role="alert">
        Giriş Başarılı.
        </div>';
    } else {
        echo '<div class="alert alert-danger" role="alert">
        Giriş Başarısız. Email veya şifre hatalı.
        </div>';
    }
   
}

if (isset($_POST["addWord"])) {
    session_start();
    $newWord = $_POST["newWord"];
    $newSentence = $_POST["newSentence"];
    $userId = $_SESSION["user_id"];

    $ekleKelime = "INSERT INTO kelime (k_uye_id, k_tanim_id, k_cumle) VALUES ('$userId', '$newWord', '$newSentence')";
    $calistirKelime = mysqli_query($baglanti, $ekleKelime);

    if ($calistirKelime) {
        echo '<div class="alert alert-success" role="alert">
        Kelime başarıyla eklendi.
        </div>';
        
        $aramaSayisiSorgu = "SELECT * FROM arama_sayilari WHERE kelime_id IN (SELECT k_id FROM kelime WHERE k_tanim_id = '$newWord')";
        $aramaSayisiSonuc = mysqli_query($baglanti, $aramaSayisiSorgu);

        if (mysqli_num_rows($aramaSayisiSonuc) > 0) {
            
            $updateAramaSayisiSorgu = "UPDATE arama_sayilari SET adet = adet + 1 WHERE kelime_id IN (SELECT k_id FROM kelime WHERE k_tanim_id = '$newWord')";
            mysqli_query($baglanti, $updateAramaSayisiSorgu);
        } else {

            $kelimeIdSorgu = "SELECT k_id FROM kelime WHERE k_tanim_id = '$newWord'";
            $kelimeIdSonuc = mysqli_query($baglanti, $kelimeIdSorgu);
            $kelimeId = mysqli_fetch_assoc($kelimeIdSonuc)['k_id'];
            $ekleAramaSayisiSorgu = "INSERT INTO arama_sayilari (kelime_id, adet) VALUES ('$kelimeId', 1)";
            mysqli_query($baglanti, $ekleAramaSayisiSorgu);
        }
    } else {
        echo '<div class="alert alert-danger" role="alert">
        Kelime eklenirken problem oluştu.
        </div>';
    }
}

if (isset($_GET['arama'])) {
    $aramaKelime = $_GET['arama'];
    $aramaSayisiSorgu = "SELECT * FROM arama_sayilari WHERE kelime_id IN (SELECT k_id FROM kelime WHERE k_tanim_id = ?)";
    $stmt = $baglanti->prepare($aramaSayisiSorgu);
    $stmt->bind_param("s", $aramaKelime);
    $stmt->execute();
    $aramaSayisiSonuc = $stmt->get_result();

    if ($aramaSayisiSonuc->num_rows > 0) {

        $updateAramaSayisiSorgu = "UPDATE arama_sayilari SET adet = adet + 1 WHERE kelime_id IN (SELECT k_id FROM kelime WHERE k_tanim_id = ?)";
        $stmt = $baglanti->prepare($updateAramaSayisiSorgu);
        $stmt->bind_param("s", $aramaKelime);
        $stmt->execute();
    } else {

        $kelimeIdSorgu = "SELECT k_id FROM kelime WHERE k_tanim_id = ?";
        $stmt = $baglanti->prepare($kelimeIdSorgu);
        $stmt->bind_param("s", $aramaKelime);
        $stmt->execute();
        $kelimeIdSonuc = $stmt->get_result();

        if ($kelimeIdSonuc->num_rows > 0) {
            $kelimeId = $kelimeIdSonuc->fetch_assoc()['k_id'];

            $ekleAramaSayisiSorgu = "INSERT INTO arama_sayilari (kelime_id, adet) VALUES (?, 1)";
            $stmt = $baglanti->prepare($ekleAramaSayisiSorgu);
            $stmt->bind_param("i", $kelimeId);
            $stmt->execute();
        } else {
            echo "Hedef kelime bulunamadı.";
        }
    }

    $populerKelimelerSorgu = "SELECT k_tanim_id, adet FROM arama_sayilari JOIN kelime ON arama_sayilari.kelime_id = kelime.k_id ORDER BY adet DESC LIMIT 5";
    $populerKelimelerSonuc = mysqli_query($baglanti, $populerKelimelerSorgu);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="index.css" rel="stylesheet">
<script src="index.js"></script>
    <title>Sözlük</title>
</head>
<body>
<div class="top-bar">
    <a href="#" class="brand"><b></b></a>
    <div class="user-actions" style="margin-right: 20px;">
        <?php if (!isset($_SESSION["user_id"])): ?>
            <a href="#" class="action" onclick="showRegistrationModal()">Kayıt Ol</a>
            <a href="#" class="action" onclick="showLoginModal()">Giriş Yap</a>
        <?php else: ?>
            <span style="color: white; margin-right: 7px; font-size: 18px; margin-top: 7px;">Hoş geldin, <?php echo $_SESSION["user_name"]; ?></span>

            <a href="http://localhost/dictionary-web-app/dictionary.php" class="action" onclick="logout()">Çıkış Yap</a>
        <?php endif; ?>
    </div>
</div>

<div class="center">
    <h1 id="sozlukBasligi">Sözlük.</h1>
   <div></div>
   <div style="display: flex; justify-content: center; align-items: center; gap: 10px;">
        <form method="GET" id="aramaForm">
            <input type="text" name="arama" id="aramaInput" placeholder="Kelime Ara" oninput="updateSozlukBasligi(this.value)" value="">
        </form>

        <?php if (isset($_SESSION["user_id"])): ?>
            <button class="add-word-button" onclick="showAddWordModal()">Ekle</button>
        <?php endif; ?>
    </div>

    <?php
if (isset($_GET['arama'])) {
    $aramaKelime = mysqli_real_escape_string($baglanti, $_GET['arama']);

    $aramaSorgusu = "SELECT * FROM kelime WHERE k_tanim_id LIKE '%$aramaKelime%' OR k_cumle LIKE '%$aramaKelime%'";
    $aramaSonuclari = mysqli_query($baglanti, $aramaSorgusu);

    if (mysqli_num_rows($aramaSonuclari) > 0) {
            
        while ($row = mysqli_fetch_assoc($aramaSonuclari)) {
            echo '<p><strong>KELİME:</strong> ' . $row['k_tanim_id'] . ' / <strong>CÜMLE:</strong> ' . $row['k_cumle'] . '</p>';

            break;
        }
    } else {
        echo '<p>Arama sonuç bulunamadı.</p>';
    }
}
?>
    <div class="tables-container">
    <table id="populerKelimeler">
        <caption><b>Popüler Kelimeler</b></caption>
        <tr>
            <th>Kelime</th>
            <th>Cümle</th>
        </tr>

        <?php
        $populerKelimelerSorgu = "SELECT k_tanim_id, k_cumle FROM kelime ORDER BY k_tarih DESC LIMIT 5";
        $populerKelimelerCalistir = mysqli_query($baglanti, $populerKelimelerSorgu);

        while ($row = mysqli_fetch_assoc($populerKelimelerCalistir)) {
            echo '<tr>';
            echo '<td>' . $row['k_tanim_id'] . '</td>';
            echo '<td>' . $row['k_cumle'] . '</td>';
            echo '</tr>';
        }
        ?>

    </table>

    <table id="sonEklenenler">
        <caption><b>Son Eklenenler</b></caption>
        <tr>
            <th>kelime</th>
            <th>Cümle</th>
            
        </tr>

        <?php
        $sonEklenenlerSorgu = "SELECT k_id, k_tanim_id, k_cumle FROM kelime ORDER BY k_tarih DESC LIMIT 5";
        $sonEklenenlerCalistir = mysqli_query($baglanti, $sonEklenenlerSorgu);

        while ($row = mysqli_fetch_assoc($sonEklenenlerCalistir)) {
            echo '<tr>';
            
            echo '<td>' . $row['k_tanim_id'] . '</td>';
            echo '<td>' . $row['k_cumle'] . '</td>';
            echo '</tr>';
        }
        ?>

    </table>
</div>
</div>

 <div class="add-word-modal" id="addWordModal">
    <div class="add-word-form">
        <h2>Yeni Kelime Ekle</h2>
        <form method="POST">
            <p>Kelime: <input type="text" name="newWord"></p>
            <p>Cümle: <input type="text" name="newSentence"></p>
            <button type="submit" name="addWord">Kelime Ekle</button>
        </form>
        <button onclick="hideAddWordModal()">Kapat</button>
    </div>
</div>

<div class="modal-overlay" id="registrationModal">
    <div class="modal-content">
        <h2>Kayıt Ol</h2>
        <div class="registration-form">
            <form method="POST">
                <p>Username: <input type="text" name="kullaniciadi"></p>
                <p>Email: <input type="email" name="email"></p>
                <p>Password: <input type="password" name="parola"></p>
                <button type="submit" name="kaydet">Kayıt Ol</button>
            </form>
        </div>
    </div>
</div>

<div class="modal-overlay" id="loginModal">
    <div class="modal-content">
        <h2>Giriş Yap</h2>
        <div class="login-form">
            <form method="POST">
                <p>Email: <input type="email" name="loginEmail"></p>
                <p>Password: <input type="password" name="loginPassword"></p>
                <button type="submit" name="login">Giriş Yap</button>
            </form>
        </div>
    </div>
</div>

<div class="fixed-bottom-bar">
    <p style="color: white; text-align: center; margin: 0;">Copyright 2024 © All rights Reserved by Enes Muratgül</p>
</div>
</body>

</html>
