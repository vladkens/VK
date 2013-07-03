<!doctype html>
    <meta charset="utf-8" />
    <style>
    html, body { font-family: monospace; }
    </style>

<?php

/**
 * Example 3.
 * Usage VK API having access token.
 * @link http://vk.com/developers.php VK API
 */

error_reporting(E_ALL);
require_once('../src/VK/VK.php');
require_once('../src/VK/VKException.php');

$vk_config = array(
    'app_id'        => '{YOUR_APP_ID}',
    'api_secret'    => '{YOUR_API_SECRET}',
    'access_token'  => '{YOUR_ACCESS_TOKEN}'
);

try {
    $vk = new VK\VK($vk_config['app_id'], $vk_config['api_secret'], $vk_config['access_token']);
    
    $user_friends = $vk->api('friends.get', array(
        'uid'       => '12345',
        'fields'    => 'uid,first_name,last_name',
        'order'     => 'name'
    ));
    
    foreach ($user_friends['response'] as $key => $value) {
        echo $value['first_name'] . ' ' . $value['last_name'] . ' ('
            . $value['uid'] . ')<br />';
    }
    
} catch (VK\VKException $error) {
    echo $error->getMessage();
}

?>