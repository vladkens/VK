<!doctype html>
    <meta charset="utf-8">
    <style>
    html, body { font-family: monospace; }
    </style>

<?php

/**
 * Example 2.
 * Get access token via OAuth and usage VK API.
 * @link http://vk.com/developers.php VK API
 */

error_reporting(E_ALL);

require_once('VK.php');

$vk_config = array(
    'app_id'        => '{YOUR_APP_ID}',
    'api_secret'    => '{YOUR_API_SECRET}',
    'callback_url'  => 'http://{YOUR_DOMAIN}/example-2.php',
    'api_settings'  => '{ACCESS_RIGHTS_THROUGH_COMMA}' //friends
);

try {
    $vk = new VK($vk_config['app_id'], $vk_config['api_secret']);
    
    if (!isset($_REQUEST['code'])) {
        $authorize_url = $vk->getAuthorizeURL(
            $vk_config['api_settings'], $vk_config['callback_url']);
            
        echo '<a href=\'' . $authorize_url . '\'>Sing in with VK</a>';
    } else {
        $access_token = $vk->getAccessToken($_REQUEST['code']);
        
        echo 'access token: ' . $access_token['access_token']
            . '<br>expires: ' . $access_token['expires_in'] . ' sec.'
            . '<br>user id: ' . $access_token['user_id'] . '<br>';
            
        $user_friends = $vk->api('friends.get', array(
            'uid'       => '12345',
            'fields'    => 'uid,first_name,last_name',
            'order'     => 'name'
        ));
        
        foreach ($user_friends['response'] as $key => $value) {
            echo $value['first_name'] . ' ' . $value['last_name'] . ' ('
                . $value['uid'] . ')<br>';
        }
    }
} catch (VKException $error) {
    echo $error->getMessage();
}