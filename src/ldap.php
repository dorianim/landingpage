<?php

defined('L_EXEC') or die();


/**
 * This class writes directly to $_SESSION['auth']:
 * loggedIn: bool
 * userDN: string
 * userName: string
 * displayName: string
 * email: string
 * firstPasswordIsStillActive: bool
 * groups: Array<string>
 */
class LandingpageLdapAuthenticator
{

  private $_ldapDs;
  private $_ldapConfig;
  private $_lastResult;

  public function __construct($config)
  {
    $this->_ldapConfig = $config;
  }

  public function lastResult()
  {
    return $this->_lastResult;
  }

  private function _result($success, $message = '')
  {
    $this->_lastResult = $message;
    return $success;
  }

  private function _bindToLdapAsAdmin()
  {
    if ($this->_ldapConfig['debug']) {
      ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
    }

    if ($this->_ldapConfig['ignoreTlsCertificateErrors']) {
      ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
    } else if (isset($this->_ldapConfig['tlsCaCertificatePath'])) {
      ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_HARD);
      ldap_set_option(null, LDAP_OPT_X_TLS_CACERTFILE, $this->_ldapConfig['tlsCaCertificatePath']);
    }

    $this->_ldapDs = ldap_connect($this->_ldapConfig['host']);

    if (!$this->_ldapDs) {
      return $this->_result(false, 'ldapConnectFailed');
    }

    ldap_set_option($this->_ldapDs, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($this->_ldapDs, LDAP_OPT_REFERRALS, 0);
    ldap_set_option($this->_ldapDs, LDAP_OPT_NETWORK_TIMEOUT, 10);

    if ($this->_ldapConfig['useTls'] && ldap_start_tls($this->_ldapDs) === false) {
      return $this->_result(false, "ldapTlsInitializationFailed");
    }

    if (!ldap_bind($this->_ldapDs, $this->_ldapConfig['binduser'], $this->_ldapConfig['binduserPassword'])) {
      if (ldap_error($this->_ldapDs) == "Can't contact LDAP server")
        return $this->_result(false, 'ldapConnectFailed');
      else
        return $this->_result(false, 'bindingToLdapAdminFailed');
    }

    return $this->_result(true);
  }

  public function findUser($username)
  {
    if (!$this->_bindToLdapAsAdmin())
      return array(
        'success' => $this->_result(false, 'ldapBindFailed')
      );

    $username = ldap_escape($username, "", LDAP_ESCAPE_FILTER);
    $filter = "(&(" . $this->_ldapConfig['userFilter'] . ")(" . $this->_ldapConfig['usernameField'] . "=$username))";
    $search = @ldap_search($this->_ldapDs, $this->_ldapConfig['basedn'], $filter);

    if (!$search || !ldap_count_entries($this->_ldapDs, $search) === 1)
      return array(
        'success' => $this->_result(false, 'ldapSearchFailed')
      );


    $userEntry = ldap_first_entry($this->_ldapDs, $search);

    return array(
      'success' => $this->_result(true),
      'userEntry' => $userEntry
    );
  }

  public function checkIfFirstPasswordIsStillActive($userEntry)
  {
    $firstPassword = ldap_get_values($this->_ldapDs, $userEntry, $this->_ldapConfig['firstPasswordField'])[0];
    if (!isset($firstPassword))
      return false;

    return ldap_bind($this->_ldapDs, ldap_get_dn($this->_ldapDs, $userEntry), $firstPassword);
  }

  public function initSession($userEntry, $firstPasswordIsStillActive)
  {
    $_SESSION['auth']['loggedIn'] = true;
    $_SESSION['auth']['userDN'] = ldap_get_dn($this->_ldapDs, $userEntry);
    $_SESSION['auth']['userName'] = ldap_get_values($this->_ldapDs, $userEntry, $this->_ldapConfig['usernameField'])[0];
    $_SESSION['auth']['displayName'] = ldap_get_values($this->_ldapDs, $userEntry, $this->_ldapConfig['displaynameField'])[0];
    $_SESSION['auth']['email'] = ldap_get_values($this->_ldapDs, $userEntry, $this->_ldapConfig['emailField'])[0];
    $_SESSION['auth']['firstPasswordIsStillActive'] = $firstPasswordIsStillActive;
    $_SESSION['auth']['firstEmailIsStillActive'] = preg_match($this->_ldapConfig['firstEmailPattern'], $_SESSION['auth']['email']);

    // calculate ldap groups (got this from: https://github.com/opnsense/core/blob/9679471d906631fe5f55efdda5b1174734278622/src/opnsense/mvc/app/library/OPNsense/Auth/LDAP.php#L477)
    $ldap_groups = array();
    foreach (ldap_get_values($this->_ldapDs, $userEntry, "memberof") as $member) {
      if (stripos($member, "cn=") === 0) {
        $ldap_groups[strtolower(explode(",", substr($member, 3))[0])] = $member;
      }
    }

    $_SESSION['auth']['groups'] = $ldap_groups;
  }

  public function authenticateUser($username, $password, $deauthenticateOnFailure = true)
  {
    $user = $this->findUser($username);

    if (!$user['success'])
      return false;

    $userEntry = $user['userEntry'];

    $bindResult = ldap_bind($this->_ldapDs, ldap_get_dn($this->_ldapDs, $userEntry), $password);

    if (!$bindResult) {
      if ($deauthenticateOnFailure)
        $this->logoutUser();

      return $this->_result(false, 'loginFailed');
    }

    $firstPasswordIsStillActive = ldap_get_values($this->_ldapDs, $userEntry, $this->_ldapConfig['firstPasswordField'])[0] === $password;
    $this->initSession($userEntry, $firstPasswordIsStillActive);

    return $this->_result(true, 'loginSuccess');

  }

  public function logoutUser()
  {
    unset($_SESSION['auth']);
  }

  public function changeUserPassword($oldPassword, $newPassword)
  {

    if (!$this->authenticateUser($_SESSION['auth']['userName'], $oldPassword, false)) {
      if ($this->_lastResult === "loginFailed") {
        return $this->_result(false, 'oldPasswordIsWrong');
      }
      return false;
    }

    if ($newPassword === $oldPassword) {
      return $this->_result(false, 'newPasswordMustNotBeEqualToOldPassword');
    } else if (strlen($newPassword) < 7) {
      return $this->_result(false, 'passwordIsTooShort');
    } else if (!preg_match("/[^A-Za-z]/", $newPassword)) {
      return $this->_result(false, 'passwordDoesNotContainANumberOrSpecialCharacter');
    } else if (!preg_match("/[a-zA-Z]/", $newPassword)) {
      return $this->_result(false, 'passwordDoesNotContainALetter');
    } else if (!preg_match("/[A-Z]/", $newPassword)) {
      return $this->_result(false, 'passwordDoesNotContainAnUppercaseLetter');
    } else if (!preg_match("/[a-z]/", $newPassword)) {
      return $this->_result(false, 'passwordDoesNotContainALowercaseLetter');
    }

    if (!$this->_bindToLdapAsAdmin()) {
      return false;
    }

    $entry = [];
    $entry['unicodePwd'] = iconv("UTF-8", "UTF-16LE", '"' . $newPassword . '"');

    if (!ldap_modify($this->_ldapDs, $_SESSION['auth']['userDN'], $entry)) {
      return $this->_result(false, 'passwordChangeLdapError');
    }

    if (!ldap_modify($this->_ldapDs, $_SESSION['auth']['userDN'], $entry)) {
      return $this->_result(false, 'passwordChangeLdapError');
    }

    $_SESSION['auth']['firstPasswordIsStillActive'] = false;
    return $this->_result(true, 'passwordChangedSuccessfully');
  }

  public function changeUserEmail($email)
  {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return $this->_result(false, 'invalidEmailError');
    }
    $this->_bindToLdapAsAdmin();
    $entry = [];
    $entry[$this->_ldapConfig['emailField']] = array($email);
    $result = ldap_mod_replace($this->_ldapDs, $_SESSION['auth']['userDN'], $entry);
    if ($result) {
      $_SESSION['auth']['firstEmailIsStillActive'] = false;
      $_SESSION['auth']['email'] = $email;
      return $this->_result(true, 'emailChangedSuccessfully');
    } else {
      return $this->_result(false, 'emailChangeLdapError');
    }
  }
}