<?php

/**
 * This is the index file of the Itsblue ldandingpage
 * 
 * PHP Version 7.4
 * 
 * @category Utilities
 * @package  Landingpage
 * @author   Dorian Zedler <dorian@itsblue.de>
 * @license  GNU AGPL V3
 * @link     github.com/itsblue/landingpage
 */

$config = [];

$themeConfig['mainIcon'] = "/assets/user_black.png";

$config['teheme'] = $themeConfig;

// LDAP
$ldapconfig['enable'] = false;
$ldapconfig['host'] = '';
$ldapconfig['port'] = '';
$ldapconfig['basedn'] = '';
$ldapconfig['binduser'] = '';
$ldapconfig['binduserPassword'] = '';
$ldapconfig['userFilter'] = '';
$ldapconfig['usernameField'] = 'samaccountname';
$ldapconfig['emailField'] = 'sophomorixCustom1';
$ldapconfig['displaynameField'] = 'displayname';
$ldapconfig['firstPasswordField'] = 'sophomorixFirstPassword';
$ldapconfig['firstEmailPattern'] = '/.*\@linuxmuster\.lan$/';

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

// Translatable strings
$translations = [];
$config['translations'] = $translations;

define('L_EXEC', true);

require_once './config.php';
require_once './translations.php';
require_once './theme.php';
require_once './ldap.php';

class ItsblueUserLandingPage
{

  private $_path;
  private $_basepath;
  private $_loginEnabled;
  private $_translations;
  private $_jitsiConfig;

  private $_authenticator;
  private $_theme;

  public function __construct($config)
  {
    $this->_loginEnabled = $config['ldap']['enable'];
    $this->_translations = $config['translations'];
    $this->_jitsiConfig = $config['jitsi'];

    session_start();

    $this->_authenticator = new LandingpageLdapAuthenticator($config['ldap']);

    $config['theme']['loginEnabled'] = $this->_loginEnabled;

    $this->_theme = new LandingpageTheme($config['theme'], $this->_filterLinks($config['links']), $config['translations']);

    $this->_calculateBasepath();
    $this->_processRequest();
  }

  private function _processRequest()
  {
    $this->_updatePermissions();
    $this->_checkPagePermissions();

    if ($this->_stringEndsWith($this->_path, "submit")) {
      if($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->_redirect('/');
      }

      switch ($this->_path) {
        case '/login/submit':
          $this->_handleLoginSubmit();
          break;

        case '/logout/submit':
          $this->_authenticator->logoutUser();
          $this->_redirect('/login');
          break;
  
        case '/changePassword/submit':
          $this->_handlePasswordChangeSubmit();
          break;
  
        case '/changeEmail/submit':
          $this->_handleEmailChangeSubmit();
          break;
  
        case '/generateJitsiLink/submit':
          $this->_handleGenerateJitsiLinkSubmit();
          break;
  
        default:
          $this->_redirect("/");
          break;
      }

    }
    else {
      if($this->_path === 'logout')
        $this->_redirect('/');

      $this->_theme->printPage(str_replace("/", "", $this->_path));
      unset($_SESSION['generateJitsiLinkLink']);
    }

    unset($_SESSION['lastResult']);
  }

  private function _calculateBasepath() {
    if (in_array("mod_rewrite", apache_get_modules()))
      $this->_basepath = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname($_SERVER['SCRIPT_FILENAME']));
    else
      $this->_basepath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']);

    if (strpos($_SERVER['REQUEST_URI'], $this->_basepath) === false || $_SERVER['REQUEST_URI'] === $this->_basepath)
      $this->_path = "/";
    else
      $this->_path = str_replace($this->_basepath, "", $_SERVER['REQUEST_URI']);
  }

  private function _redirect($path)
  {
    header('Location: ' . $this->_basepath . $path);
    die();
  }

  // -----------------------
  // - Permission handlers -
  // -----------------------

  private function _updatePermissions()
  {
    $_SESSION['auth']['permissions'][''] = false;
    $_SESSION['auth']['permissions']['login'] = $this->_loginEnabled && !$this->_isUserAuthenticated();
    $_SESSION['auth']['permissions']['logout'] = $this->_loginEnabled && $this->_isUserAuthenticated();
    $_SESSION['auth']['permissions']['links'] = 
      !$this->_loginEnabled 
      || ($this->_isUserAuthenticated() && !($_SESSION['auth']['firstPasswordIsStillActive'] || $_SESSION['auth']['firstEmailIsStillActive']));
    
    $_SESSION['auth']['permissions']['changePassword'] = 
      $this->_isUserAuthenticated() 
      && !(!$_SESSION['auth']['firstPasswordIsStillActive'] && $_SESSION['auth']['firstEmailIsStillActive']);
    
    $_SESSION['auth']['permissions']['changeEmail'] = $this->_isUserAuthenticated() && !($_SESSION['auth']['firstPasswordIsStillActive']);
    
    $_SESSION['auth']['permissions']['generateJitsiLink'] =
      ($this->_isUserAuthenticated() || !$this->_loginEnabled)
      && $this->_jitsiConfig['enable']
      && $this->_isUserPartOfGroups($this->_jitsiConfig['limitToGroups'])
      && !($_SESSION['auth']['firstPasswordIsStillActive'] || $_SESSION['auth']['firstEmailIsStillActive']);
  }

  private function _checkPagePermissions()
  {
    $pageRedirectOnInsufficientPermissionsPriority = [
      0 => "links",
      1 => "generateJitsiLink",
      2 => "changePassword",
      3 => "changeEmail",
      4 => "login"
    ];

    $page = explode("/", $this->_path)[1];

    if ($_SESSION['auth']['permissions'][$page] === false) {
      if (!$this->_isUserAuthenticated()) {
        $_SESSION['lastResult'] = 'loginRequired';
      } else if ($this->_isUserAuthenticated() && !($_SESSION['auth']['firstPasswordIsStillActive'] || $_SESSION['auth']['firstEmailIsStillActive'])) {
        // Do not throw an Error when redirecting to changePassword or changeEmail
        $_SESSION['lastResult'] = 'permissionDenied';
      }

      if ($this->_path === '/' || $this->_path === '') {
        // if the root is opened, do not throw an error!
        unset($_SESSION['lastResult']);
      }

      // redirect to the first page the user has access to
      foreach ($pageRedirectOnInsufficientPermissionsPriority as $page) {
        if ($_SESSION['auth']['permissions'][$page])
          $this->_redirect("/" . $page);
      }

      die($this->_translations['results']['noPermissionToAnyPage']);
    }
    else if(!isset($_SESSION['auth']['permissions'][$page])) {
      $this->_redirect('/');
    }
  }

  private function _filterLinks($links) {
    if(!$this->_loginEnabled)
      return $links;

    $filteredLinks = [];

    foreach($links as $linkName => $linkMeta) {
      if (isset($linkMeta['limitToGroups']) && !$this->_isUserPartOfGroups($linkMeta['limitToGroups']))
        continue;
      else
        $filteredLinks[$linkName] = $linkMeta;
    }

    return $filteredLinks;
  }

  // -------------------
  // - Submit handlers -
  // -------------------

  private function _handleLoginSubmit()
  {
    if ($this->_authenticator->authenticateUser($_POST['username'], $_POST['password'])) {
      $_SESSION['lastResult'] = $this->_authenticator->lastResult();

      $this->_redirect('/');
    } else {
      $_SESSION['lastResult'] = $this->_authenticator->lastResult();
      $this->_redirect('/login');
    }
  }

  private function _handlePasswordChangeSubmit()
  {
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    $newPasswordRepeat = $_POST['newPasswordRepeat'];

    if ($newPassword !== $newPasswordRepeat) {
      $_SESSION['lastResult'] = 'newPasswordAndRepeatDidNotMatch';
      $this->_redirect('/changePassword');
    }

    $this->_authenticator->changeUserPassword($oldPassword, $newPassword);
    $_SESSION['lastResult'] = $this->_authenticator->lastResult();
    $this->_redirect('/changePassword');
  }

  private function _handleEmailChangeSubmit()
  {
    if (!isset($_POST['email']))
      $this->_redirect('/');

    $redirectToHome = $_SESSION['auth']['firstEmailIsStillActive'];

    if ($this->_authenticator->changeUserEmail($_POST['email']) && $redirectToHome) {
      $_SESSION['lastResult'] = $this->_authenticator->lastResult();
      $this->_redirect('/');
    } else {
      $_SESSION['lastResult'] = $this->_authenticator->lastResult();
      $this->_redirect('/changeEmail');
    }
  }

  private function _handleGenerateJitsiLinkSubmit()
  {
    if (!isset($_POST['room']) || $_POST['room'] == '') {
      $_SESSION['lastResult'] = 'generateJitsiLinkRoomMustNotBeEmpty';
      $this->_redirect('/generateJitsiLink');
    }

    // Create token header as a JSON string
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

    // Create token payload as a JSON string
    $payload = json_encode([
      'iss' => $this->_jitsiConfig['applicationId'],
      'sub' => $this->_jitsiConfig['applicationId'],
      'iat' => time(),
      'nbf' => time(),
      'exp' => time() + 3600,
      'aud' => $this->_jitsiConfig['applicationId'],
      'room' => $_POST['room'],
      'context' => [
        'user' => [
          'name' => $_SESSION['auth']['displayName']
        ]
      ]
    ]);

    // Encode Header to Base64Url String
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

    // Encode Payload to Base64Url String
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->_jitsiConfig['applicationSecret'], true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

    if(!$this->_stringEndsWith($this->_jitsiConfig['host'], '/'))
      $this->_jitsiConfig['host'] .= '/';

    $_SESSION['generateJitsiLinkLink'] = $this->_jitsiConfig['host'] . $_POST['room'] . "?jwt=" . $jwt;
    $_SESSION['lastResult'] = 'generateJitsiLinkSuccessfull';
    $this->_redirect('/generateJitsiLink');
  }

  // ----------------------------
  // - General helper functions -
  // ----------------------------

  private function _isUserAuthenticated()
  {
    return $_SESSION['auth']['loggedIn'];
  }

  // checks if user is part of at least one of the given groups
  private function _isUserPartOfGroups($groups)
  {
    if (is_array($groups)) {
      if (count($groups) <= 0) {
        return true;
      } else {
        foreach ($groups as $group) {
          if (array_key_exists($group, $_SESSION['auth']['groups']))
            return true;
        }
        return false;
      }
    } else {
      if (!isset($groups)) {
        return true;
      } else {
        return array_key_exists($groups, $_SESSION['auth']['groups']);
      }
    }
  }

  private function _stringStartsWith($haystack, $needle)
  {
    $length = strlen($needle);
    return substr($haystack, 0, $length) === $needle;
  }

  private function _stringEndsWith($haystack, $needle)
  {
    $length = strlen($needle);
    if (!$length) {
      return true;
    }
    return substr($haystack, -$length) === $needle;
  }
}

new ItsblueUserLandingPage($config);
die();
