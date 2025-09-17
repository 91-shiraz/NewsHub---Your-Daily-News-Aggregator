<?php

return (object)[
    'db' => (object) [
        'host' => '127.0.0.1',
        'dbname' => 'news_aggregator',
        'user' => 'root',
        'password' => '12345678',
        'charset'=> 'utf8mb4',
    ],

    'news_api_key' => 'af180bd5f3ec42918944e0c24bc16c3f',

    'news_api_endpoint'=> 'https://newsapi.org/v2/top-headlines',

    'country'=> 'US',

    'page_size'=> 50,
];