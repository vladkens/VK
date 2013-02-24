<?php

/**
 * The PHP class for vk.com API and to support OAuth.
 * @author Vlad Pronsky <vladkens@yandex.ru>
 * @license http://www.gnu.org/licenses/gpl.html GPL v3
 * @version 0.1.3
 */

class VK
{
    /**
     * VK application ID.
     * @var int
     */
    private $app_id;
    
    /**
     * VK application secret key.
     * @var string
     */
    private $api_secret;
    
    /**
     * VK access token.
     * @var string
     */
    private $access_token;
    
    /**
     * Set timeout.
     * @var int
     */
    private $timeout = 30;
    
    /**
     * Set connect timeout.
     * @var int
     */
    private $connecttimeout = 30;
    
    /**
     * Check SLL certificate.
     * @var bool
     */
    private $ssl_verifypeer = false;
    
    /**
     * Set library version.
     * @var string
     */
    private $lib_version    = '0.1';
    
    /**
     * Contains the last HTTP status code returned.
     * @var int
     */
    private $http_code;
    
    /**
     * Contains the last HTTP headers returned.
     * @var mixed See http://www.php.net/manual/en/function.curl-getinfo.php
     */
    private $http_info;
    
    /**
     * Authorization status.
     * @var bool
     */
    private $auth = false;
    
    /**
     * Set base API URLs.
     */
    public function baseAuthorizeURL()   { return 'http://oauth.vk.com/authorize'; }
    public function baseAccessTokenURL() { return 'https://oauth.vk.com/access_token'; }
    public function getAPI_URL($method)  { return 'https://api.vk.com/method/' . $method; }
    
    /**
     * @param   string  $app_id
     * @param   string  $api_secret
     * @param   string  $access_token
     * @return  void
     */
    public function __construct($app_id, $api_secret, $access_token = null) {
        $this->app_id       = $app_id;
        $this->api_secret   = $api_secret;
        $this->access_token = $access_token;
        
        if (!is_null($this->access_token) && !$this->checkAccessToken()) {
            throw new VKException('Invalid access token.');
        } else {
            $this->auth = true;
        }
    }
    
    /**
     * Returns authorization status.
     * @return  bool    true is auth, false is not auth
     */
    public function is_auth() {
        return $this->auth;
    }
    
    /**
     * VK API method.
     * @param   string  $method     Contains VK API method.
     * @param   array   $parameters Contains settings call.
     * @return  array
     */
    public function api($method, $parameters = array()) {
        $parameters['timestamp']    = time();
        $parameters['api_id']       = $this->app_id;
        $parameters['random']       = rand(0, 10000);
        $parameters['format']       = 'json';
        $parameters['v']            = $this->lib_version;
        
        if (!is_null($this->access_token))
            $parameters['access_token'] = $this->access_token;
            
        ksort($parameters);
        
        $sig = '';
        foreach ($parameters as $key => $value) {
            $sig .= $key . '=' . $value;
        }
        $sig .= $this->api_secret;
        
        $parameters['sig'] = md5($sig);
        $query = $this->createURL($parameters, $this->getAPI_URL($method));
        
        return $this->http($query);
    }
    
    /**
     * Get authorize URL.
     * @param   string  $api_settings   Access rights requested by your app (through comma).
     * @param   string  $callback_url   Callback url.
     * @param   bool    $test_mode
     * @return  string
     */
    public function getAuthorizeURL($api_settings = '', $callback_url = 'http://oauth.vk.com/blank.html',
        $test_mode = false)
    {
        $parameters = array(
            'client_id'     => $this->app_id,
            'scope'         => $api_settings,
            'redirect_uri'  => $callback_url,
            'response_type' => 'code'
        );
        
        if ($test_mode) {
            $parameters['test_mode'] = '1';
        }
        
        return $this->createURL($parameters, $this->baseAuthorizeURL());
    }
    
    /**
     * Get the access token.
     * @param   string  $code           The code to get access token.
     * @param   string  $callback_url   Callback URL
     * @return  array(
     *      access_token,
     *      expires_in,
     *      user_id)
     */
    public function getAccessToken($code, $callback_url = 'http://oauth.vk.com/blank.html')
    {
        if (!is_null($this->access_token) && $this->auth) {
            throw new VKException('Already authorized.');
        }
        
        $parameters = array(
            'client_id'     => $this->app_id,
            'client_secret' => $this->api_secret,
            'code'          => $code,
            'redirect_uri'  => $callback_url
        );
        
        $url = $this->createURL($parameters, $this->baseAccessTokenURL());
        
        $rs  = $this->http($url);

        if (isset($rs['error'])) {
            $message = 'HTTP status code: ' . $this->http_code . '. ' . $rs['error'];
            if (isset($rs['error_description'])) $message .= ': ' . $rs['error_description'];
            throw new VKException($message);
        } else {
            $this->auth = true;
            $this->access_token = $rs['access_token'];
            return $rs;
        }
    }
    
    /**
     * Make HTTP request.
     * @param   string  $url
     * @param   string  $method     Get or Post
     * @param   array   $postfields If $method post
     * @return  array   API return
     */
    private function http($url, $method = 'GET', $postfields = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT,      'VK v' . $this->lib_version);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT,        $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            
            if (!is_null($postfields)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            }
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        
        $rs = curl_exec($ch);
        $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->http_info = curl_getinfo($ch);
        curl_close($ch);
        
        return json_decode($rs, true);
    }
    
    /**
     * Create URL from the sended parameters.
     * @param   array   $parameters Add to base url
     * @param   string  $url        Base url 
     * @return  string 
     */
    private function createURL($parameters, $url) {
        $piece = array();
        foreach ($parameters as $key => $value)
            $piece[] = $key . '=' . rawurlencode($value);
        
        $url .= '?' . implode('&', $piece);
        return $url;
    }
    
    /**
     * Check freshness of access token.
     * @return  bool    true is valid access token else false
     */
    private function checkAccessToken() {
        if (is_null($this->access_token)) return false;
        
        $response = $this->api('getUserSettings');
        return isset($response['response']);
    }
    
}

class VKException extends Exception {  }

?>