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

// Server
$serverConfig['hideIndexPhp'] = false;
$serverConfig['publicAccessToLinks'] = false;
$serverConfig['theme'] = "default-theme";
$serverConfig['language'] = "de-DE";

// Customization
$customizationConfig['organizationName'] = "ExampleOrg";
$customizationConfig['fullOrganizationName'] = "Example Organization e.V.";
$customizationConfig['supportEmailAddress'] = "support@example.com";

// Theme
$themeConfig['mainIcon'] = "/assets/user_black.png";

// Links
$links = [];

// Translation overrides
$translationOverrides = [];

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

define('L_EXEC', true);

require_once './config.php';
$config['server'] = $serverConfig;
$config['theme'] = $themeConfig;
$config['ldap'] = $ldapconfig;
$config['jitsi'] = $jitsiconfig;
$config['links'] = $links;
$config['downloads'] = $downloads;

require_once './translations/' . $config['server']['language'] . '.php';

// apply transltion overrides
$config['translations'] = array_replace_recursive($translations, $translationOverrides);

require_once './' . $config['server']['theme'] . '.php';
require_once './ldap.php';

class ItsblueUserLandingPage
{

  private $_path;
  private $_basepath;
  private $_loginEnabled;
  private $_translations;
  private $_jitsiConfig;
  private $_serverConfig;
  private $_downloads;

  private $_authenticator;
  private $_theme;

  public function __construct($config)
  {
    $this->_loginEnabled = $config['ldap']['enable'];
    $this->_translations = $config['translations'];
    $this->_jitsiConfig = $config['jitsi'];
    $this->_serverConfig = $config['server'];
    $this->_downloads = $config['downloads'];

    session_start();

    $this->_authenticator = new LandingpageLdapAuthenticator($config['ldap']);

    $config['theme']['loginEnabled'] = $this->_loginEnabled;

    $this->_theme = new LandingpageTheme($config['theme'], $this->_filterLinks($config['links']), $this->_translations);

    $this->_calculateBasepath();
    $this->_processRequest();
  }

  private function _processRequest()
  {
    $this->_updatePermissions();
    $this->_checkPagePermissions();

    if ($this->_stringEndsWith($this->_path, "submit")) {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->_redirect('/');
      }

      switch ($this->_path) {
        case '/login/submit':
          $this->_handleLoginSubmit();
          break;

        case '/logout/submit':
          $this->_authenticator->logoutUser();
          if (!$this->_serverConfig['publicAccessToLinks'])
            $this->_redirect('/login');
          else
            $this->_redirect('/');
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
    } else {
      if ($this->_path === '/logout') {
        $this->_redirect('/');
      } else if ($this->_stringStartsWith($this->_path, '/dl')) {
        $fileId = explode('/', ltrim($this->_path, '/'), 2)[1];

        if ($fileId === "" || !isset($fileId)) {
          $this->_redirect('/');
        }

        $this->_sendFile($fileId);
      }

      $this->_theme->printPage(str_replace("/", "", $this->_path));
      unset($_SESSION['generateJitsiLinkLink']);
    }

    unset($_SESSION['lastResult']);
  }

  private function _calculateBasepath()
  {
    if ($this->_serverConfig['hideIndexPhp'])
      $this->_basepath = str_replace(basename($_SERVER["SCRIPT_NAME"]), '', $_SERVER['SCRIPT_NAME']);
    else
      $this->_basepath = $_SERVER["SCRIPT_NAME"];

    $this->_basepath = rtrim($this->_basepath, "/ ");

    if (($this->_basepath !== '' && strpos($_SERVER['REQUEST_URI'], $this->_basepath) === false) || $_SERVER['REQUEST_URI'] === $this->_basepath)
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
    $_SESSION['permissions'][''] = false;
    $_SESSION['permissions']['dl'] = true;
    $_SESSION['permissions']['login'] = $this->_loginEnabled && !$this->_isUserAuthenticated();
    $_SESSION['permissions']['logout'] = $this->_loginEnabled && $this->_isUserAuthenticated();
    $_SESSION['permissions']['links'] =
      !$this->_loginEnabled
      || ($this->_isUserAuthenticated() && !($_SESSION['auth']['firstPasswordIsStillActive'] || $_SESSION['auth']['firstEmailIsStillActive']))
      || (!$this->_isUserAuthenticated() && $this->_serverConfig['publicAccessToLinks']);

    $_SESSION['permissions']['changePassword'] =
      $this->_isUserAuthenticated()
      && !(!$_SESSION['auth']['firstPasswordIsStillActive'] && $_SESSION['auth']['firstEmailIsStillActive']);

    $_SESSION['permissions']['changeEmail'] = $this->_isUserAuthenticated() && !($_SESSION['auth']['firstPasswordIsStillActive']);

    $_SESSION['permissions']['generateJitsiLink'] =
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

    if (!isset($_SESSION['permissions'][$page])) {
      $this->_redirect('/');
    } else if ($_SESSION['permissions'][$page] === false) {
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
        if ($_SESSION['permissions'][$page])
          $this->_redirect("/" . $page);
      }

      die($this->_translations['results']['noPermissionToAnyPage']);
    }
  }

  private function _filterLinks($links)
  {
    if (!$this->_loginEnabled)
      return $links;

    $filteredLinks = [];

    foreach ($links as $linkName => $linkMeta) {
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

    if (!$this->_stringEndsWith($this->_jitsiConfig['host'], '/'))
      $this->_jitsiConfig['host'] .= '/';

    $_SESSION['generateJitsiLinkLink'] = $this->_jitsiConfig['host'] . $_POST['room'] . "?jwt=" . $jwt;
    $_SESSION['lastResult'] = 'generateJitsiLinkSuccessfull';
    $this->_redirect('/generateJitsiLink');
  }

  private function _sendFile($fileId)
  {
    if (array_key_exists($fileId, $this->_downloads)) {
      $filePath = $this->_downloads[$fileId]['path'];
      header('Content-type: ' . $this->_downloads[$fileId]['content-type']);
      header("Content-Disposition: attachment; filename=\"" . $this->_downloads[$fileId]['downloadName'] . "\"");
      http_response_code(200);
      readfile($filePath);
    } else {
      http_response_code(404);
    }

    die();
  }

  // ----------------------------
  // - General helper functions -
  // ----------------------------

  private function _isUserAuthenticated()
  {
    $authenticated =
      isset($_SESSION['auth'])
      && isset($_SESSION['auth']['loggedIn'])
      && $_SESSION['auth']['loggedIn'] === true
      && isset($_SESSION['auth']['userDN']);

    if (!$authenticated && isset($_SESSION['auth'])) {
      unset($_SESSION['auth']);
    }

    return $authenticated;
  }

  // checks if user is part of at least one of the given groups
  private function _isUserPartOfGroups($groups)
  {
    if (!isset($_SESSION['auth']) || !isset($_SESSION['auth']['groups']))
      return false;

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
