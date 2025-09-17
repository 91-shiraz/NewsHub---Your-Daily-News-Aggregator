<?php

declare(strict_types= 1);

$config = require __DIR__ . '/config.php';

$dsn = "mysql:host={$config->db->host};dbname={$config->db->dbname};charset={$config->db->charset}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $config->db->user, $config->db->password, $options);
} catch (PDOException $e) {
    echo "Database Connection Failed: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

/**
 * Basic Sanitization
*/

function sanitize_text(?string $text): ?string{
    if ($text === null) return null;
    $text = trim($text);
    $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);
    return $text === '' ? null : $text;
}

/**
 * Validate URL
*/

function sanitize_url(?string $url): ?string{
    if ($url === null) return null;
    $url = trim($url);
    if(filter_var($url, FILTER_VALIDATE_URL)) return $url;
    return null;
}

/**
 * Fetch News From News API using cURL
*/

function fetch_news_from_api($config, $params = []){
    $endpoint = $config->news_api_endpoint;
    $params = array_merge( [
        'apiKey'=> $config->news_api_key,
        'country'=> $config->country,
        'pageSize'=> $config->page_size,
    ], $params );

    $url = $endpoint . '?' . http_build_query($params);

    $ch = curl_init();
   
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Api-Key: ' . $config->news_api_key,
        'User-Agent: ' . 'NewsAggregatorApp/1.0'        
    ]);
   
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($response === false){
        throw new RuntimeException("cURL Error: {$error}");
    }

    if($code !== 200){
        throw new RuntimeException("NewsAPI  Returned HTTP Error: {$code} {$response}");
    }

    $data = json_decode($response, true);

    if(!is_array($data)){
        throw new RuntimeException("NewsAPI  Returned Invalid JSON: {$response}");
    }

    return $data;
}

$categories = [ 'business', 'entertainment', 'general', 'health', 'science', 'sports', 'technology'];
$inserted_count = 0;

$stmt = $pdo->prepare("
    INSERT INTO articles (title, description, url, image_url, source, category, published_date)
    VALUES (:title, :description, :url, :image_url, :source, :category, :published_date)
    ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    url = VALUES(url),
    image_url = VALUES(image_url),
    source = VALUES(source),
    category = VALUES(category),
    published_date = VALUES(published_date)
");

foreach($categories as $category){
    try{
        $payload = fetch_news_from_api($config, ['category'=> $category]);
    }catch(Exception $e){
        echo "Error Fetching News for Category {$category}: {$e->getMessage()}" . PHP_EOL;
        continue;
    }

    if(!isset($payload["articles"]) || !is_array($payload["articles"])) continue;

    foreach($payload["articles"] as $news){
        $title = sanitize_text($news["title"] ?? null);
        $description = sanitize_text($news["description"] ?? null);
        $url = sanitize_url($news["url"] ?? null);
        $image_url = sanitize_url($news["urlToImage"] ?? null);
        $source = sanitize_text($news["source"]["name"] ?? null);
        $article_category = sanitize_text($news["category"] ?? $category);
        $published_date = date('Y-m-d H:i:s', strtotime($news["publishedAt"] ?? null));

        if(!$title || !$url) continue;

        try{
            $stmt->execute([
                ':title' => $title,
                ':description'=> $description,
                ':url'=> $url,
                ':image_url'=> $image_url,
                ':source'=> $source,
                ':category'=> $article_category,
                ':published_date'=> $published_date,
            ]);
            $inserted_count += $stmt->rowCount();
        }catch(PDOException $e){
            echo "Error Inserting News: {$e->getMessage()}" . PHP_EOL;
            continue;
        }
    }
}
