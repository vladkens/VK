<!doctype html>
    <meta charset="utf-8" />
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
require_once('../src/VK/VK.php');
require_once('../src/VK/VKException.php');

$vk_config = array(
    'app_id'        => '{YOUR_APP_ID}',
    'api_secret'    => '{YOUR_API_SECRET}',
    'callback_url'  => 'http://{YOUR_DOMAIN}/samples/example-2.php',
    'api_settings'  => '{ACCESS_RIGHTS_THROUGH_COMMA}' // In this example use 'friends'.
    // If you need infinite token use key 'offline'.
);

try {
    $vk = new VK\VK($vk_config['app_id'], $vk_config['api_secret']);
    
    if (!isset($_REQUEST['code'])) {
        /**
         * If you need switch the application in test mode,
         * add another parameter "true". Default value "false".
         * Ex. $vk->getAuthorizeURL($api_settings, $callback_url, true);
         */
        $authorize_url = $vk->getAuthorizeURL(
            $vk_config['api_settings'], $vk_config['callback_url']);
            
        echo '<a href="' . $authorize_url . '">Sign in with VK</a>';
    } else {
        $access_token = $vk->getAccessToken($_REQUEST['code'], $vk_config['callback_url']);
        
        echo 'access token: ' . $access_token['access_token']
            . '<br />expires: ' . $access_token['expires_in'] . ' sec.'
            . '<br />user id: ' . $access_token['user_id'] . '<br /><br />';
            
        $user_friends = $vk->api('friends.get', array(
            'uid'       => '12345',
            'fields'    => 'uid,first_name,last_name',
            'order'     => 'name'
        ));
        
        foreach ($user_friends['response'] as $key => $value) {
            echo $value['first_name'] . ' ' . $value['last_name'] . ' ('
                . $value['uid'] . ')<br />';
        }
    }
} catch (VK\VKException $error) {
    echo $error->getMessage();
}
