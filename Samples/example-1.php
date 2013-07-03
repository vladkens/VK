<!doctype html>
    <meta charset="utf-8" />
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
require_once('../src/VK/VK.php');
require_once('../src/VK/VKException.php');

try {
    $vk = new VK\VK('{YOUR_APP_ID}', '{YOUR_API_SECRET}'); // Use your app_id and api_secret
    
    $users = $vk->api('users.get', array(
        'uids'   => '1234,4321',
        'fields' => 'first_name,last_name,sex'));
        
    foreach ($users['response'] as $user) {
        echo $user['first_name'] . ' ' . $user['last_name'] . ' (' .
            ($user['sex'] == 1 ? 'Girl' : 'Man') . ')<br />';
    }
    
} catch (VK\VKException $error) {
    echo $error->getMessage();
}
