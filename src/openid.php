<?php

defined('L_EXEC') or die();

use Jumbojett\OpenIDConnectClient;

class LandingpageOpenid
{
    private $_openidConfig;
    private $_serverConfig;
    private $_client;
    public function __construct($openidConfig, $serverConfig)
    {
        $this->_openidConfig = $openidConfig;
        $this->_serverConfig = $serverConfig;
        $this->_openidConfig['issuer'] = rtrim($this->_openidConfig['issuer'], '/');

        $this->_validateConfig();


        $this->_client = new OpenIDConnectClient(
            $this->_openidConfig['issuer'],
            $this->_openidConfig['clientId'],
            $this->_openidConfig['clientSecret']
        );
        $this->_client->addAuthParam(array('response_mode' => 'form_post'));
        $this->_client->setRedirectURL($this->_serverConfig['externalUrl'] . '/login/submit');
        $this->_client->addScope('openid');
        $this->_client->addScope('profile');
    }

    private function _validateConfig()
    {
        if (!$this->enabled()) {
            return true;
        }

        $requiredFields = [
            'issuer',
            'clientId',
            'clientSecret'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($this->_openidConfig[$field]) || !$this->_openidConfig[$field]) {
                throw new Exception('The field openid.' . $field . ' is required when openid is enabled.');
            }
        }

        if (!isset($this->_serverConfig['externalUrl']) || !$this->_serverConfig['externalUrl']) {
            throw new Exception('The field server.externalUrl is required when openid is enabled.');
        }
    }

    public function enabled()
    {
        return $this->_openidConfig['enable'];
    }

    public function login()
    {
        $this->_client->authenticate();
    }

    public function logout()
    {
        $this->_client->signOut($_SESSION['openid']['idToken'], $this->_serverConfig['externalUrl'] . '/');
    }

    public function callback()
    {
        try {
            $this->_client->authenticate();
        } catch (Exception $e) {
            return array(
                'success' => false
            );
        }
        $userinfo = $this->_client->requestUserInfo();
        $_SESSION['openid']['idToken'] = $this->_client->getIdToken();
        return array(
            'success' => true,
            'userinfo' => $userinfo
        );
    }
}