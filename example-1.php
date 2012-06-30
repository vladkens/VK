<!doctype html>
    <meta charset="utf-8">
    <style>
    html, body { font-family: monospace; }
    </style>

<?php

/**
 * Example 1.
 * Usage VK API without authorization.
 * Some calls are not available.
 * @link http://vk.com/developers.php VK API
 */

error_reporting(E_ALL);

require_once('VK.php');

$vk_config = array(
    'app_id'        => '{YOUR_APP_ID}',
    'api_secret'    => '{YOUR_API_SECRET}'
);

try {
    $vk = new VK($vk_config['app_id'], $vk_config['api_secret']);
    
    $users = $vk->api('users.get', array('uids' => '12345,54321',
        'fields' => 'first_name,last_name,nickname'));
    
    foreach ($users['response'] as $key => $value) {
        $user = $value['user'];
        echo $user['first_name'] . ' «' . $user['nickname']
            . '» ' . $user['last_name'] . '<br>';
    }
    
} catch (VKException $error) {
    echo $error->getMessage();
}