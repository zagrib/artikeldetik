<?php

$title = "";
$content = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['url'])) {

    $url = trim($_POST['url']);

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $error = "URL tidak valid.";
    } else {

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => "Mozilla/5.0"
        ]);

        $html = curl_exec($ch);
        curl_close($ch);

        if (!$html) {
            $error = "Gagal mengambil halaman.";
        } else {

            libxml_use_internal_errors(true);

            $dom = new DOMDocument();
            $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);

            $xpath = new DOMXPath($dom);

            // Ambil Judul
            $titleNode = $xpath->query('//h1[contains(@class,"detail__title")]');

            if ($titleNode->length > 0) {
                $title = trim($titleNode->item(0)->textContent);
            }

            // Ambil Body
            $bodyNode = $xpath->query('//div[contains(@class,"detail__body-text")]');

            if ($bodyNode->length > 0) {

                $body = $bodyNode->item(0);

                // Hapus iklan
                while (($ads = $xpath->query('.//*[contains(@class,"staticdetail_container")]', $body))->length > 0) {
                    $ads->item(0)->parentNode->removeChild($ads->item(0));
                }

                // Hapus baca juga
                while (($ads = $xpath->query('.//*[contains(@class,"noncontent")]', $body))->length > 0) {
                    $ads->item(0)->parentNode->removeChild($ads->item(0));
                }

                // Hapus script
                while (($ads = $xpath->query('.//script', $body))->length > 0) {
                    $ads->item(0)->parentNode->removeChild($ads->item(0));
                }

                foreach ($xpath->query('.//p', $body) as $p) {

                    $text = trim(html_entity_decode($p->textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                    if ($text != "") {
                        $content .= $text . "\n\n";
                    }

                }

                $content = trim($content);
            }

        }

    }

}

?>
<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Detik Article Extractor</title>

<style>

body{
    font-family:Arial,sans-serif;
    background:#f5f5f5;
    margin:0;
    padding:30px;
}

.container{
    max-width:1000px;
    margin:auto;
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,.1);
}

h2{
    margin-top:0;
}

input[type=text]{
    width:100%;
    padding:12px;
    font-size:16px;
    box-sizing:border-box;
}

button{
    margin-top:15px;
    padding:12px 25px;
    font-size:16px;
    cursor:pointer;
    background:#007bff;
    color:#fff;
    border:none;
    border-radius:5px;
}

button:hover{
    background:#0056b3;
}

textarea{
    width:100%;
    box-sizing:border-box;
    padding:10px;
    margin-top:10px;
    font-size:15px;
    resize:vertical;
}

.label{
    margin-top:20px;
    font-weight:bold;
}

.error{
    color:red;
    margin-top:15px;
}

</style>

</head>

<body>

<div class="container">

<h2>Detik Article Extractor</h2>

<form method="post">

<input
type="text"
name="url"
placeholder="Masukkan URL artikel detik.com"
value="<?php echo isset($_POST['url']) ? htmlspecialchars($_POST['url']) : ''; ?>"
>

<button type="submit">
Extract Artikel
</button>

</form>

<?php if($error!=""){ ?>

<div class="error">
<?php echo $error; ?>
</div>

<?php } ?>

<div class="label">
Judul Artikel
</div>

<textarea rows="3" readonly><?php echo htmlspecialchars($title); ?></textarea>

<div class="label">
Isi Artikel
</div>

<textarea rows="20" readonly><?php echo htmlspecialchars($content); ?></textarea>

</div>

</body>
</html>
