<?php

function extractDetikArticle($url)
{
    $context = stream_context_create([
        'http' => [
            'header' => implode("\r\n", [
                "User-Agent: Mozilla/5.0",
                "Accept-Language: id-ID,id;q=0.9,en;q=0.8"
            ])
        ]
    ]);

    $html = file_get_contents($url, false, $context);

    if (!$html) {
        return false;
    }

    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    // Judul
    $titleNode = $xpath->query('//h1[contains(@class,"detail__title")]')->item(0);

    $title = "";
    if ($titleNode) {
        $title = trim($titleNode->textContent);
    }

    // Body artikel
    $bodyNode = $xpath->query('//div[contains(@class,"detail__body-text")]')->item(0);

    $article = "";

    if ($bodyNode) {

        // Hapus iklan, baca juga, script, style
        $removeXpath = new DOMXPath($dom);

        foreach ($removeXpath->query('.//script|.//style|.//*[contains(@class,"noncontent")]|.//*[contains(@class,"staticdetail_container")]', $bodyNode) as $remove) {
            $remove->parentNode->removeChild($remove);
        }

        // Ambil semua paragraf
        foreach ($bodyNode->getElementsByTagName("p") as $p) {
            $text = trim($p->textContent);

            if ($text != "") {
                $article .= $text . "\n\n";
            }
        }
    }

    return [
        "title" => $title,
        "content" => trim($article)
    ];
}

// Contoh

$url = "https://www.detik.com/jatim/hukum-dan-kriminal/d-8530413/maling-pengirim-surat-maaf-4-hari-berturut-turut-temui-korban-kenapa";

$data = extractDetikArticle($url);

header('Content-Type: text/plain; charset=utf-8');

echo $data['title'];

echo "\n\n";

echo $data['content'];

?>
