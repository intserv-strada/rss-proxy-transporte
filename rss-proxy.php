<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

function fetchFeed($url) {
    // Configura cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignora problemas de certificado
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Segue redirecionamentos
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $data = curl_exec($ch);
    curl_close($ch);

    if (!$data) {
        return [];
    }

    // Carrega XML com suporte a namespaces
    $xml = @simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
    if (!$xml) {
        return [];
    }

    $items = [];

    // Verifica se é formato RSS ou Atom
    if (isset($xml->channel->item)) {
        foreach ($xml->channel->item as $item) {
            $ns_media = $item->children('media', true);
            $image = '';

            if ($ns_media && isset($ns_media->content)) {
                $image = (string) $ns_media->content->attributes()->url;
            }

            $items[] = [
                "title" => (string) $item->title,
                "link" => (string) $item->link,
                "description" => strip_tags((string) $item->description),
                "pubDate" => (string) $item->pubDate,
                "image" => $image
            ];
        }
    } elseif (isset($xml->entry)) {
        foreach ($xml->entry as $entry) {
            $items[] = [
                "title" => (string) $entry->title,
                "link" => (string) $entry->link['href'],
                "description" => strip_tags((string) $entry->summary),
                "pubDate" => (string) $entry->updated,
                "image" => ''
            ];
        }
    }

    return $items;
}

// Lista de feeds
$feeds = [
    "https://www.gov.br/antt/pt-br/assuntos/rss.xml",
    "https://www.gov.br/dnit/pt-br/assuntos/rss.xml",
    "https://www.gov.br/prf/pt-br/assuntos/rss.xml",
    "https://www.gov.br/senatran/pt-br/assuntos/rss.xml",
    "https://www.cnt.org.br/rss/noticias"
];

// Busca e combina todos
$allNews = [];
foreach ($feeds as $feed) {
    $allNews = array_merge($allNews, fetchFeed($feed));
}

// Ordena por data
usort($allNews, function($a, $b) {
    return strtotime($b['pubDate']) - strtotime($a['pubDate']);
});

// Retorna os 20 mais recentes
echo json_encode(array_slice($allNews, 0, 20), JSON_UNESCAPED_UNICODE);