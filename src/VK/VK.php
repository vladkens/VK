<?php

/**
 * The PHP class for vk.com API and to support OAuth.
 * @author Vlad Pronsky <vladkens@yandex.ru>
 * @license https://raw.github.com/vladkens/VK/master/LICENSE MIT
 * @version 0.1.6
 */
 
namespace VK;
 
class VK
{
    /**
     * VK application id.
     * @var string
     */
    private $app_id;
    
    /**
     * VK application secret key.
     * @var string
     */
    private $api_secret;
    
    /**
     * API version. If null uses latest version.
     * @var int
     */
    private $api_version;
    
    /**
     * VK access token.
     * @var string
     */
    private $access_token;
    
    /**
     * Authorization status.
     * @var bool
     */
    private $auth = false;
    
    /**
     * Instance curl.
     * @var resourse
     */
    private $ch;
    
    const AUTHORIZE_URL    = 'https://oauth.vk.com/authorize';
    const ACCESS_TOKEN_URL = 'https://oauth.vk.com/access_token';

    /**
     * Constructor.
     * @param   string  $app_id
     * @param   string  $api_secret
     * @param   string  $access_token
     * @throws  VKException
     * @return  void
     */
    public function __construct($app_id, $api_secret, $access_token = null)
    {
        $this->app_id       = $app_id;
        $this->api_secret   = $api_secret;
        $this->access_token = $access_token;
        
        $this->ch = curl_init();
        
        if (!is_null($this->access_token)) {
            if (!$this->checkAccessToken()) {
                throw new VKException('Invalide access token.');
            } else {
                $this->auth = true;
            }
        }
    }
    
    /**
     * Destructor.
     * @return  void
     */
    public function __destruct()
    {
        curl_close($this->ch);
    }
    
    /**
     * Set special API version.
     * @param   int     $version
     * @return  void
     */
    public function setApiVersion($version)
    {
        $this->api_version = $version;
    }
    
    /**
     * Returns base API url. 
     * @param   string  $method
     * @param   string  $response_format
     * @return  string
     */
    public function getApiUrl($method, $response_format = 'json')
    {
        return 'https://api.vk.com/method/' . $method . '.' . $response_format;
    }
    
    /**
     * Returns authorization link with passed parameters.
     * @param   string  $api_settings
     * @param   string  $callback_url
     * @param   bool    $test_mode
     * @return  string
     */
    public function getAuthorizeUrl($api_settings = '',
        $callback_url = 'https://api.vk.com/blank.html', $test_mode = false)
    {
        $parameters = array(
            'client_id'     => $this->app_id,
            'scope'         => $api_settings,
            'redirect_uri'  => $callback_url,
            'response_type' => 'code'
        );
        
        if ($test_mode)
            $parameters['test_mode'] = 1;
            
        return $this->createUrl(self::AUTHORIZE_URL, $parameters);
    }
    
    /**
     * Returns access token by code received on authorization link.
     * @param   string  $code
     * @param   string  $callback_url
     * @throws  VKException
     * @return  array
     */
    public function getAccessToken($code, $callback_url = 'https://api.vk.com/blank.html')
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
        
        $rs = json_decode($this->request(
            $this->createUrl(self::ACCESS_TOKEN_URL, $parameters)), true);

        if (isset($rs['error'])) {
            throw new VKException($rs['error'] .
                (!isset($rs['error_description']) ?: ': ' . $rs['error_description']));
        } else {
            $this->auth = true;
            $this->access_token = $rs['access_token'];
            return $rs;
        }
    }
    
    /**
     * Return user authorization status.
     * @return  bool
     */
    public function isAuth()
    {
        return $this->auth;
    }
    
    /**
     * Check for validity access token.
     * @return  bool
     */
    private function checkAccessToken()
    {
        if (is_null($this->access_token)) return false;
        
        $rs = $this->api('getUserSettings');
        return isset($rs['response']);
    }
    
    /**
     * Execute API method with parameters and return result.
     * @param   string  $method
     * @param   array   $parameters
     * @param   string  $format
     * @return  mixed
     */
    public function api($method, $parameters = array(), $format = 'array')
    {
        $parameters['timestamp'] = time();
        $parameters['api_id']    = $this->app_id;
        $parameters['random']    = rand(0, 10000);
        
        if (!is_null($this->access_token)) $parameters['access_token'] = $this->access_token;
        if (!is_null($this->api_version)) $parameters['v'] = $this->api_version;
        ksort($parameters);
        
        $sig = '';
        foreach ($parameters as $key => $value) {
            $sig .= $key . '=' . $value;
        }
        $sig .= $this->api_secret;
        
        $parameters['sig'] = md5($sig);
        
        if ($method == 'execute')
        {
            $rs = $this->request(
                $this->getApiUrl($method, $format == 'array' ? 'json' : $format), "POST", $parameters);
        }
        else
        {
            $rs = $this->request($this->createUrl(
                $this->getApiUrl($method, $format == 'array' ? 'json' : $format), $parameters));
        }
        return $format == 'array' ? json_decode($rs, true) : $rs;
    }
    
    /**
     * Concatinate keys and values to url format and return url.
     * @param   string  $url
     * @param   array   $parameters
     * @return  string
     */
    private function createUrl($url, $parameters)
    {
        $piece = array();
        foreach ($parameters as $key => $value)
            $piece[] = $key . '=' . rawurlencode($value);
        
        $url .= '?' . implode('&', $piece);
        return $url;
    }
    
    /**
     * Executes request on link.
     * @param   string  $url
     * @param   string  $method
     * @param   array   $postfields
     * @return  string
     */
    private function request($url, $method = 'GET', $postfields = array())
    {
        curl_setopt_array($this->ch, array(
            CURLOPT_USERAGENT       => 'VK/1.0 (+https://github.com/vladkens/VK))',
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_POST            => ($method == 'POST'),
            CURLOPT_POSTFIELDS      => $postfields,
            CURLOPT_URL             => $url
        ));
        
        return curl_exec($this->ch);
    }
    
};
    
