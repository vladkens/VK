<?php

/**
 * Require PHP >= 5.4
 * Search, play and download audio files use VK API.
 * Before downloading files are renamed in the format "%artist% - %title%"
 */

ini_set('default_charset', 'utf-8');
error_reporting(E_ALL);
require_once('../../src/VK/VK.php');
require_once('../../src/VK/VKException.php');

$vk_config = [
    'app_id'        => '{YOUR_APP_ID}',
    'api_secret'    => '{YOUR_API_SECRET}',
    'access_token'  => '{YOUR_ACCESS_TOKEN}' // To get access token see exmaple-2.php
];

try {
    $vk = new VK\VK($vk_config['app_id'], $vk_config['api_secret'], $vk_config['access_token']);

    // Rename and download audio file
    if (isset($_GET['download']) && !empty($_GET['download'])) {
        $rs = $vk->api('audio.getById', ['audios' => $_GET['download']]);
        if (isset($rs['response'])) {
            $rs = $rs['response'][0];
            header('Content-Type: application/force-download');
            header('Content-Disposition: attachment; filename="'.$rs['artist'].' - '.$rs['title'].'.mp3"');
            readfile($rs['url']);
        }
        die();
    } else { // Get audio list from query string
        $q  = isset($_GET['q']) ? $_GET['q'] : '';
        $rs = [];
        
        if (!empty($q)) {
            $rs = $vk->api('audio.search', [
                'v' => '2.0',
                'q' => $q
            ]);
        }
    }
} catch (VK\VKException $error) {
    die($error->getMessage());
}

?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Search and download mp3</title>
    
    <script src="script.js"></script>
    <script>
        // Copy vk access token to js global namesapce
        var vk_access_token = '<?= $vk_config['access_token']; ?>';
    </script>
    <style>
    body {
        font-family: Tahoma;
        font-size: 12px;
        background-color: #efefef;
        color: #333;
    }
    body a {
        color: grey;
    }
    .center {
        width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: #fff;
        box-shadow: 0 0 10px rgba(55,55,55,0.5);
    }
    .search-string {
        margin: 0 auto;
        width: 460px;
    }
    .search-string input[type=search] {
        width: 390px;
    }
    .search-result ul {
        list-style-type: none;
    }
    .search-result li {
        position: relative;
        display: block;
        border-bottom: 1px solid #ccc;
    }
    .search-result a {
        padding: 8px 12px;
        text-decoration: none;
    }
    .search-result a.download {
        position: absolute;
    }
    .search-result a.download:hover {
        background-color: #ddd;
    }
    .search-result a.play {
        display: block;
        margin-left: 30px;
    }
    .search-result a.play:hover {
        background-color: #efefef;
    }
    .search-result li audio {
        width: 100%;
    }
    </style>
</head>
<body>
    <div class="center">
        <div class="search-string">
            <form method="get" action="" >
                <input type="search" name="q" value="<?= $q; ?>" placeholder="Letters enter her" />
                <input type="submit" value="Search" />
            </form>
        </div>
        <div class="search-result">
            <ul>
            <?php if (isset($rs['response'])) {
                foreach ($rs['response'] as $value) {
                    if (!is_array($value)) continue;
                    $aid = $value['owner_id'].'_'.$value['aid'];
                    echo '<li data-id="'.$aid.'">';
                    echo '<a class="download" href="?download='.$aid.'" title="Download">&darr;</a>';
                    echo '<a class="play" href="#play='.$aid.'">'.$value['artist'].' — '.$value['title'];
                    echo ' ('.floor($value['duration']/60).':'.(($value['duration']%60<10?'0':'').$value['duration']%60).')</a>';
                    echo '</li>';
                }
            } ?>
            </ul>
        </div>
    </div>
</body>
</html>