<?php

defined('L_EXEC') or die();

class LandingpageConfigManager {

    public function __construct($configFilePath) {
        $this->_configFilePath = $configFilePath;
    }

    public function load() {
        $config = $this->_loadDefaults();

        if(file_exists($this->_configFilePath)) {
            $config = array_replace_recursive($config, array_filter(yaml_parse_file($this->_configFilePath)));
        }

        return $config;
    }

    public function store($config) {
        return file_put_contents($this->_configFilePath, yaml_emit($config));
    }

    public function migrate() {
        // Convert old php config to new yaml config.
        if(file_exists('/data/config.php') && !file_exists($this->_configFilePath)) {
            require_once '/data/config.php';
            $config['server'] = $serverConfig;
            $config['theme'] = $themeConfig;
            $config['ldap'] = $ldapconfig;
            $config['jitsi'] = $jitsiconfig;
            $config['links'] = $links;
            $config['downloads'] = $downloads;
            $config['customization'] = $customizationConfig;
            $config['translationOverrides'] = $translationOverrides;
            if(!$this->store(array_filter($config))) {
                echo "Error writing new config. See https://github.com/dorianim/landingpage/issues/2";
                return FALSE;
            }

            if(!rename("/data/config.php", "/data/config.php.old")) {
                echo "Error renaming old config. See https://github.com/dorianim/landingpage/issues/2";
                return FALSE;
            }
        }

        $config = $this->load();

        // Convert uncategoriezed links
        if($this->_getArrayDepth($config['links']) < 4) {
            $config['links'] = array(
                'Services' => array(
                    "links" => $config['links']
                )
            );
            if(!$this->store($config)) {
                echo "Error writing new config. See https://github.com/dorianim/landingpage/issues/4";
                return FALSE;
            }
        }

        return TRUE;
    }

    private function _loadDefaults() {
        $config = [];

        // Server
        $serverConfig['publicAccessToLinks'] = false;
        $serverConfig['theme'] = "default-theme";
        $serverConfig['language'] = "de-DE";
        $config['server'] = $serverConfig;

        // Customization
        $customizationConfig['organizationName'] = "ExampleOrg";
        $customizationConfig['fullOrganizationName'] = "Example Organization e.V.";
        $customizationConfig['supportEmailAddress'] = "support@example.com";
        $config['customization'] = $customizationConfig;

        // Theme
        $config['theme']['mainIcon'] = "https://upload.wikimedia.org/wikipedia/commons/thumb/0/04/User_icon_1.svg/120px-User_icon_1.svg.png";

        // Translation overrides
        $config['translationOverrides'] = [];

        // LDAP
        $ldapconfig['enable'] = false;
        $ldapconfig['debug'] = false;
        $ldapconfig['host'] = '';
        $ldapconfig['useTls'] = false;
        $ldapconfig['ignoreTlsCertificateErrors'] = false;
        $ldapconfig['tlsCaCertificatePath'] = '';
        $ldapconfig['basedn'] = '';
        $ldapconfig['binduser'] = '';
        $ldapconfig['binduserPassword'] = '';
        $ldapconfig['userFilter'] = '';
        $ldapconfig['usernameField'] = 'samaccountname';
        $ldapconfig['emailField'] = 'mail';
        $ldapconfig['displaynameField'] = 'displayname';
        $ldapconfig['firstPasswordField'] = 'sophomorixFirstPassword';
        $ldapconfig['firstEmailPattern'] = '/^$/';

        $config['ldap'] = $ldapconfig;

        // Jitsi
        $jitsiconfig['enable'] = false;
        $jitsiconfig['host'] = '';
        $jitsiconfig['applicationSecret'] = '';
        $jitsiconfig['applicationId'] = '';
        $jitsiconfig['limitToGroups'] = [];

        $config['jitsi'] = $jitsiconfig;

        // Links
        $config['links'] = [];

        return $config;
    }

    private function _getArrayDepth($array) {
        // Source: https://stackoverflow.com/questions/262891/is-there-a-way-to-find-out-how-deep-a-php-array-is
        $max_depth = 1;

        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = $this->_getArrayDepth($value) + 1;
    
                if ($depth > $max_depth) {
                    $max_depth = $depth;
                }
            }
        }
    
        return $max_depth;
    }

}