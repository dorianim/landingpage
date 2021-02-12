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
class LandingpageLdapAuthenticator {

  private $_ldapDs;
  private $_ldapConfig;
  private $_lastResult;

  public function __construct($config) {
    $this->_ldapConfig = $config;
  }

  public function lastResult() {
    return $this->_lastResult;
  }

  private function _result($success, $message = '') {
    $this->_lastResult = $message;
    return $success;
  }

    private function _bindToLdapAsAdmin()
    {
      $this->_ldapDs = ldap_connect($this->_ldapConfig['host'], $this->_ldapConfig['port']);

      if (!$this->_ldapDs) {
        return $this->_result(false, 'ldapConnectFailed');
      }

      ldap_set_option($this->_ldapDs, LDAP_OPT_PROTOCOL_VERSION, 3);
      ldap_set_option($this->_ldapDs, LDAP_OPT_REFERRALS, 0);
      ldap_set_option($this->_ldapDs, LDAP_OPT_NETWORK_TIMEOUT, 10);

      if (!ldap_bind($this->_ldapDs, $this->_ldapConfig['binduser'], $this->_ldapConfig['binduserPassword'])) {
        if (ldap_error($this->_ldapDs) == "Can't contact LDAP server")
          return $this->_result(false, 'ldapConnectFailed');
        else
          return $this->_result(false, 'bindingToLdapAdminFailed');
      }

      return $this->_result(true);
    }

    public function authenticateUser($username, $password, $deauthenticateOnFailure = true)
    {
      if(!$this->_bindToLdapAsAdmin())
        return false;
      $username = $this->_sanitizeStringForLdap($username);
      $filter = "(&(" . $this->_ldapConfig['userFilter'] . ")(" . $this->_ldapConfig['usernameField'] . "=$username))";
      if (($search = @ldap_search($this->_ldapDs, $this->_ldapConfig['basedn'], $filter))) {
        $number_returned = ldap_count_entries($this->_ldapDs, $search);

        if ($number_returned === 1) {
          $userEntry = ldap_first_entry($this->_ldapDs, $search);
          $userDn = ldap_get_dn($this->_ldapDs, $userEntry);

          if (ldap_bind($this->_ldapDs, $userDn, $password)) {

            $_SESSION['auth']['loggedIn'] = true;
            $_SESSION['auth']['userDN'] = $userDn;
            $_SESSION['auth']['userName'] = ldap_get_values($this->_ldapDs, $userEntry, $this->_ldapConfig['usernameField'])[0];
            $_SESSION['auth']['displayName'] = ldap_get_values($this->_ldapDs, $userEntry, $this->_ldapConfig['displaynameField'])[0];
            $_SESSION['auth']['email'] = ldap_get_values($this->_ldapDs, $userEntry, $this->_ldapConfig['emailField'])[0];
            $_SESSION['auth']['firstPasswordIsStillActive'] = ldap_get_values($this->_ldapDs, $userEntry, $this->_ldapConfig['firstPasswordField'])[0] === $password;
            $_SESSION['auth']['firstEmailIsStillActive'] = preg_match($this->_ldapConfig['firstEmailPattern'], $_SESSION['auth']['email']);

            // calculate ldap groups (got this from: https://github.com/opnsense/core/blob/9679471d906631fe5f55efdda5b1174734278622/src/opnsense/mvc/app/library/OPNsense/Auth/LDAP.php#L477)
            $ldap_groups = array();
            foreach (ldap_get_values($this->_ldapDs, $userEntry, "memberof") as $member) {
              if (stripos($member, "cn=") === 0) {
                $ldap_groups[strtolower(explode(",", substr($member, 3))[0])] = $member;
              }
            }

            $_SESSION['auth']['groups'] = $ldap_groups;

            return $this->_result(true, 'loginSuccess');
          }
        }
      } else {
        return $this->_result(false, 'ldapSearchFailed');
      }

      if ($deauthenticateOnFailure)
        $this->logoutUser();

      $this->_result(false, 'loginFailed');
    }

    public function logoutUser()
    {
      unset($_SESSION['auth']);
    }

    public function changeUserPassword($oldPassword, $newPassword)
    {

      if (!$this->authenticateUser($_SESSION['auth']['userName'], $oldPassword, false)) {
        return $this->_result(false, 'oldPasswordIsWrong');
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

      $newpw64 = $this->_adUnicodePwdValue($newPassword);
      $userDn = $_SESSION['auth']['userDN'];

      $ldif =
        <<<EOT
      dn: $userDn
      changetype: modify
      replace: unicodePwd
      unicodePwd:: $newpw64
      EOT;


      // Build LDAP command string
      $cmd = sprintf("/usr/bin/ldapmodify -H %s -D '%s' -x -w %s", "ldap://" . $this->_ldapConfig['host'], $this->_ldapConfig['binduser'], $this->_ldapConfig['binduserPassword']);

      $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
        2 => array("pipe", "w") // stderr is a file to write to
      );

      $process = proc_open($cmd, $descriptorspec, $pipes);

      if (is_resource($process)) {
        // $pipes now looks like this:
        // 0 => writeable handle connected to child stdin
        // 1 => readable handle connected to child stdout

        fwrite($pipes[0], "$ldif\n");
        fclose($pipes[0]);

        $proc_stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $proc_stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        // It is important that you close any pipes before calling
        // proc_close in order to avoid a deadlock
        $return_value = proc_close($process);

        if ($return_value > 0) {
          //$message[] = "An error occurred while changing the password! Please provide the following info to your system administrator:";
          //$message[] = "STDOUT: $proc_stdout";
          //$message[] = "STDERR: $proc_stderr";
          //$message[] = "EXIT: $return_value";

          return $this->_result(false, 'passwordChangeLdapError');
          return false;
        } else {
          $_SESSION['auth']['firstPasswordIsStillActive'] = false;
          return $this->_result(true, 'passwordChangedSuccessfully');
        }
      }
    }

    public function changeUserEmail($email)
    {
      $this->_bindToLdapAsAdmin();
      $entry = [];
      $entry[$this->_ldapConfig['emailField']] = array($email);
      $result = ldap_mod_replace($this->_ldapDs, $_SESSION['auth']['userDN'], $entry);
      if ($result) {
        // TODO: move out
        $_SESSION['auth']['firstEmailIsStillActive'] = false;
        $_SESSION['auth']['email'] = $email;
        return $this->_result(true, 'emailChangedSuccessfully');
        return true;
      } else {
        return $this->_result(false, 'emailChangeLdapError');
        return false;
      }
    }

    private function _adUnicodePwdValue($pw)
    {
      $newpw = '';
      $pw = "\"" . $pw . "\"";
      $len = strlen($pw);
      for ($i = 0; $i < $len; $i++)
        $newpw .= $pw[$i] . "\000";
      $newpw = base64_encode($newpw);
      return $newpw;
    }

    private function _sanitizeStringForLdap($string)
    {
      $sanitized = array(
        '\\' => '\5c',
        '*' => '\2a',
        '(' => '\28',
        ')' => '\29',
        "\x00" => '\00'
      );

      return str_replace(array_keys($sanitized), array_values($sanitized), $string);
    }
}

?>