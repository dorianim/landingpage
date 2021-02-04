<?php

// -----------------------------------------
// -  DON'T CHANGE ANYTHING IN THIS FILE   -
// - (unless you know what you're doing ;) -
// -----------------------------------------

$config = [];

$globalConfig['mainIcon'] = "/assets/user_black.png";

$config['globalConfig'] = $globalConfig;

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

$config['ldap'] = $ldapconfig;

// Links
$config['links'] = [];

// Translatable strings

// - Without LDAP
$translations['home']['hello'] = "Hallo";
$translations['home']['welcomeMessage'] = "Wilkommen bei deinem MakerLab-Account - Ein Login für alles";
$translations['home']['menu']['linksLabel'] = "MakerLab Dienste";
$translations['home']['menu']['changePasswordLabel'] = "Passwort ändern";
$translations['home']['menu']['changeEmailLabel'] = "E-Mail-Adresse ändern";
$translations['home']['menu']['logoutLabel'] = "Abmelden";

// - With LDAP
$translations['passwordRules'][0] = "Das Passwort muss mindestens 7 Zeichen lang sein.";
$translations['passwordRules'][1] = "Das Passwort muss mindestens eine Zahl enthalten.";
$translations['passwordRules'][2] = "Das Passwort muss mindestens einen Klein- und einen Großbuchstaben enthalten.";

$translations['results']['internalError'] = "Interner Fehler. Falls das Problem bestehen bleibt, kontaktieren sie bitte <a href=\"mailto:it@makerlab-murnau.de\">it@makerlab-murnau.de</a>";
$translations['results']['ldapConnectFailed'] = $translations['results']['internalError'] . " (#0000)";
$translations['results']['bindingToLdapAdminFailed'] = $translations['results']['internalError'] . " (#0001)";
$translations['results']['ldapSearchFailed'] = $translations['results']['internalError'] . " (#0002)";
$translations['results']['loginSuccess'] = "Erfolgreich angemeldet";
$translations['results']['loginFailed'] = "Ungültige Zugangsdaten";
$translations['results']['loginRequired'] = "Bitte zuerst anmelden!";
$translations['results']['oldPasswordIsWrong'] = "Das aktulle Passwort ist falsch";
$translations['results']['newPasswordMustNotBeEqualToOldPassword'] = "Das neue Passwort darf nicht mit dem Alten übereinstimmen";
$translations['results']['newPasswordAndRepeatDidNotMatch'] = "Die Passwörter stimmen nicht überein";
$translations['results']['passwordIsTooShort'] = $translations['passwordRules'][0];
$translations['results']['passwordDoesNotContainANumber'] = $translations['passwordRules'][1];
$translations['results']['passwordDoesNotContainALetter'] = $translations['passwordRules'][2];
$translations['results']['passwordDoesNotContainAnUppercaseLetter'] = $translations['passwordRules'][2];
$translations['results']['passwordDoesNotContainALowercaseLetter'] = $translations['passwordRules'][2];
$translations['results']['passwordChangeLdapError'] = $translations['results']['internalError'] . " (#0003)";
$translations['results']['newPasswordMustNotBeOldPassword'] = "Das neue Passwort darf nicht mit dem Alten übereinstimmen.";
$translations['results']['passwordChangedSuccessfully'] = "DeinPasswort wurde erfolgreich geändert.";
$translations['results']['emailChangeLdapError'] = $translations['results']['internalError'] . " (#0004)";
$translations['results']['emailChangedSuccessfully'] = "Deine E-Mail-Adresse wurde erfolgreich geändert.";

$translations['notifications']['changeFirstPassword'] = "Schritt 1 von 2: Du nutzt immernoch dein Erstpasswort. Dieses Passwort ist nicht sicher, bitte ändere es jetzt.";
$translations['notifications']['changeFirstEmail'] = "Schritt 2 von 2: Deine E-Mail-Adresse ist noch noch nicht hinterlegt, bitte hinterlege sie jetzt.";

$translations['globals']['title'] = "MakerLab Account";
$translations['globals']['usernameLabel'] = "Benutzername";
$translations['globals']['passwordLabel'] = "Passwort";
$translations['globals']['emailAddress'] = "E-Mail-Adresse";

$translations['login']['title'] = "Bitte anmelden";
$translations['login']['submitLabel'] = "Anmelden";
$translations['login']['footnote'] = "Login für Mitglieder des MakerLab Murnau e.V.";

$translations['links']['title'] = "Hier kannst du deinen MakerLab-Account verwenden:";

$translations['changePassword']['passwordRulesHeader'] = "Regeln für Passwörter:";
$translations['changePassword']['currentPasswordLabel'] = "Aktuelles Passwort";
$translations['changePassword']['newPasswordLabel'] = "Neues Passwort";
$translations['changePassword']['repeatNewPasswordLabel'] = "Neues Password Wiederholen";
$translations['changePassword']['submitLabel'] = "Passwort ändern";

$translations['changeEmail']['disclaimer'] = "Deine E-Mail-Adresse wird verwendet, um dir Benachrichtigungen über neue Nachrichten in Rocket.Chat und Gerätereservierungen zukommen zu lassen. Sie wird nicht an Dritte weitergegeben.";
$translations['changeEmail']['currentEmailLabel'] = "Deine aktuelle E-Mail-Adresse ist:";
$translations['changeEmail']['submitLabel'] = "E-Mail-Adresse ändern";

$config['translations'] = $translations;


require_once('./config.php');

class MlmUserLandingPage
{

  private $_path;
  private $_ldapConfig;
  private $_ldapDs;
  private $_translations;
  private $_links;
  private $_resultLevels;
  private $_globalConfig;

  public function __construct($config)
  {
    $this->_ldapConfig = $config['ldap'];
    $this->_translations = $config['translations'];
    $this->_links = $config['links'];
    $this->_globalConfig = $config['globalConfig'];

    $this->_resultLevels['loginSuccess'] = "success";
    $this->_resultLevels['loginFailed'] = "danger";
    $this->_resultLevels['ldapConnectFailed'] = "danger";
    $this->_resultLevels['ldapSearchFailed'] = "danger";
    $this->_resultLevels['bindingToLdapAdminFailed'] = "danger";
    $this->_resultLevels['loginRequired'] = "warning";
    $this->_resultLevels['oldPasswordIsWrong'] = "danger";
    $this->_resultLevels['newPasswordMustNotBeEqualToOldPassword'] = "danger";
    $this->_resultLevels['newPasswordAndRepeatDidNotMatch'] = "danger";
    $this->_resultLevels['passwordIsTooShort'] = "danger";
    $this->_resultLevels['passwordDoesNotContainANumber'] = "danger";
    $this->_resultLevels['passwordDoesNotContainALetter'] = "danger";
    $this->_resultLevels['passwordDoesNotContainAnUppercaseLetter'] = "danger";
    $this->_resultLevels['passwordDoesNotContainALowercaseLetter'] = "danger";
    $this->_resultLevels['passwordChangeLdapError'] = "danger";
    $this->_resultLevels['newPasswordMustNotBeOldPassword'] = "danger";
    $this->_resultLevels['passwordChangedSuccessfully'] = 'success';
    $this->_resultLevels['emailChangedSuccessfully'] = 'success';
    $this->_resultLevels['emailChangeLdapError'] = 'danger';

    session_start();
    $pathArray = explode(basename(__FILE__), $_SERVER['REQUEST_URI']);
    if (count($pathArray) < 2)
      $this->_path = "/";
    else
      $this->_path = $pathArray[1];

    $this->_processRequest();
  }

  private function _processRequest()
  {
    if(!$this->_ldapConfig['enable']) {
      $this->_printHome();
      die();
    }

    if (!$this->_isUserAuthenticated() && $this->_path !== '/login' && $this->_path !== '/login/submit') {
      if ($this->_path != '/')
        $_SESSION['lastResult'] = 'loginRequired';
 
      $this->_redirect("/login");
    }

    if ($this->_stringEndsWith($this->_path, "submit") && $_SERVER['REQUEST_METHOD'] !== 'POST')
      $this->_redirect('/');

    switch ($this->_path) {
      case '/login':
        if ($this->_isUserAuthenticated())
          $this->_redirect('/');
        else
          $this->_printLogin();
        break;

      case '/login/submit':
        if ($this->_isUserAuthenticated())
          $this->_redirect('/');
        else
          $this->_handleLoginSubmit();
        break;

      case '/logout':
        $this->_logoutUser();
        $this->_redirect('/login');
        break;

      case '/':
        if ($_SESSION['firstPasswordIsStillActive'])
          $this->_redirect('/changePassword');
        else if ($_SESSION['firstEmailIsStillActive'])
          $this->_redirect('/changeEmail');
        else
          $this->_printHome();
        break;

      case '/changePassword':
        if (!$_SESSION['firstPasswordIsStillActive'] && $_SESSION['firstEmailIsStillActive'])
          $this->_redirect('/changeEmail');
        else
          $this->_printHome();
        break;

      case '/changePassword/submit':
        $this->_handlePasswordChangeSubmit();
        break;

      case '/changeEmail':
        if ($_SESSION['firstPasswordIsStillActive'])
          $this->_redirect('/changePassword');
        else
          $this->_printHome();
        break;

      case '/changeEmail/submit':
        $this->_handleEmailChangeSubmit();
        break;

      default:
        $this->_redirect("/");
        break;
    }

    unset($_SESSION['lastResult']);
  }

  private function _redirect($path)
  {
    $basePath = explode(basename(__FILE__), $_SERVER['REQUEST_URI'])[0] . basename(__FILE__);
    header('Location: ' . $basePath . $path);
    die();
  }

  // -------------------
  // - Submit handlers -
  // -------------------

  private function _handleLoginSubmit()
  {
    if ($this->_authenticateUser($_POST['username'], $_POST['password'])) {
      $this->_redirect('/');
    } else {
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

    $this->_changeUserPassword($oldPassword, $newPassword);
    $this->_redirect('/changePassword');
  }

  private function _handleEmailChangeSubmit()
  {
    if (!isset($_POST['email']))
      $this->_redirect('/');

    $redirectToHome = $_SESSION['firstEmailIsStillActive'];

    if ($this->_changeUserEmail($_POST['email']) && $redirectToHome)
      $this->_redirect('/');
    else
      $this->_redirect('/changeEmail');
  }

  // ---------------------------
  // - HTML printing functions -
  // ---------------------------

  private function _printHeader($printOnlySkeleton = false)
  {
?>
    <!DOCTYPE html>
    <html>

    <head>
      <meta name="viewport" content="width=device-width, initial-scale=1">

      <!-- Bootstrap -->
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>

      <style>
        .mr-4 {
          margin-right: 1.5rem !important;
        }

        .ml-4 {
          margin-left: 1.5rem !important;
        }

        .card {
          align-items: center;
          text-align: center;
        }
      </style>

      <title><?= $this->_trId("globals.title"); ?></title>
      <?php
      if (!$printOnlySkeleton) :

      ?>
    </head>
  <?php
      endif;
    }

    private function _printResultAlert()
    {
      if (!isset($_SESSION['lastResult']) || $_SESSION['lastResult'] === 'loginSuccess')
        return;

      $this->_printAlert($this->_resultToMessage($_SESSION['lastResult']), $this->_resultLevels[$_SESSION['lastResult']]);
    }

    private function _printPasswordAndEmailChangeNotification()
    {
      if ($_SESSION['firstPasswordIsStillActive'])
        $this->_printAlert($this->_trId('notifications.changeFirstPassword'), "warning", false);
      else if ($_SESSION['firstEmailIsStillActive'])
        $this->_printAlert($this->_trId('notifications.changeFirstEmail'), "warning", false);
    }

    private function _printAlert($content, $level = 'waring', $dismissible = true)
    {
  ?>
  <div class="alert alert-<?= $level ?> <?php if ($dismissible) echo "alert-dismissible"; ?> fade show" role="alert">
    <strong><?= $content ?></strong>
    <?php if ($dismissible) : ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <?php endif; ?>
  </div>
<?php
    }

    private function _printFooter()
    {
?>

    </html>
  <?php
    }

    private function _printLogin()
    {
      $this->_printHeader(true);
  ?>

    <style>
      html,
      body {
        height: 100%;
      }

      body {
        display: flex;
        align-items: center;
        padding-top: 40px;
        padding-bottom: 40px;
        background-color: #f5f5f5;
      }

      .form-signin {
        width: 100%;
        max-width: 330px;
        padding: 15px;
        margin: auto;
      }

      .form-signin .checkbox {
        font-weight: 400;
      }

      .form-signin .form-control {
        position: relative;
        box-sizing: border-box;
        height: auto;
        padding: 10px;
        font-size: 16px;
      }

      .form-signin .form-control:focus {
        z-index: 2;
      }

      .form-signin input[type="email"] {
        margin-bottom: -1px;
        border-bottom-right-radius: 0;
        border-bottom-left-radius: 0;
      }

      .form-signin input[type="password"] {
        margin-bottom: 10px;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
      }

      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }
    </style>
    </head>

    <body class="text-center">
      <main class="form-signin">
        <form action="login/submit" method="post">
          <img class="mb-4" src="<?= $this->_globalConfig['mainIcon'] ?>" alt="" height="150">
          <h1 class="h3 mb-3 fw-normal"><?= $this->_trId("login.title"); ?></h1>
          <?php $this->_printResultAlert(); ?>
          <label for="inputEmail" class="visually-hidden"><?= $this->_trId("globals.usernameLabel"); ?></label>
          <input type="text" id="inputUsername" class="form-control" placeholder="<?= $this->_trId("globals.usernameLabel"); ?>" name="username" required autofocus>
          <label for="inputPassword" class="visually-hidden"><?= $this->_trId("globals.passwordLabel"); ?></label>
          <input type="password" id="inputPassword" class="form-control" placeholder="<?= $this->_trId("globals.passwordLabel"); ?>" name="password" required>
          <button class="w-100 btn btn-lg btn-primary" type="submit"><?= $this->_trId("login.submitLabel"); ?></button>
          <p class="mt-5 mb-3 text-muted"><?= $this->_trId("login.footnote"); ?></p>
        </form>
      </main>
    </body>
  <?php
      $this->_printFooter();
    }

    // -----------------
    // - Home Page - / -
    // -----------------

    private function _printHome()
    {
      $this->_printHeader();

      //echo "This is home :)<pre>";
      //print_r($_SESSION);
  ?>
    <main>

      <section class="py-5 text-center container">
        <div class="row py-lg-5">
          <div class="col-lg-6 col-md-8 mx-auto">
            <img class="mb-4" src="<?= $this->_globalConfig['mainIcon'] ?>" alt="" height="150">
            <?php if($this->_ldapConfig['enable']): ?>
            <h1 class="fw-light"><?= $this->_trId("home.hello"); ?> <?= $_SESSION['displayName'] ?></h1>
            <?php endif; ?>
            <p class="lead text-muted"><?= $this->_trId("home.welcomeMessage"); ?></p>
          </div>
        </div>
      </section>

      <div class="album py-5 bg-light">
        <div class="container">
        <?php if($this->_ldapConfig['enable']): ?>
          <?php $this->_printPasswordAndEmailChangeNotification();
          $this->_printResultAlert(); ?>
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link <?= $this->_getTabClasses('links'); ?>" id="pills-links-tab" data-bs-toggle="pill" href="#pills-links" role="tab" aria-controls="pills-links" aria-selected="true"><?= $this->_trId("home.menu.linksLabel"); ?></a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link <?= $this->_getTabClasses('changePassword'); ?>" id="pills-changePassword-tab" data-bs-toggle="pill" href="#pills-changePassword" role="tab" aria-controls="pills-changePassword" aria-selected="false"><?= $this->_trId("home.menu.changePasswordLabel"); ?></a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link <?= $this->_getTabClasses('changeEmail'); ?>" id="pills-changeEmail-tab" data-bs-toggle="pill" href="#pills-changeEmail" role="tab" aria-controls="pills-changeEmail" aria-selected="false"><?= $this->_trId("home.menu.changeEmailLabel"); ?></a>
              </li>
            </ul>
            <a href="logout" class="btn btn-outline-secondary"><?= $this->_trId("home.menu.logoutLabel"); ?></a>
          </div>
          <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade <?= $this->_getTabClasses('links', false); ?>" id="pills-links" role="tabpanel" aria-labelledby="pills-links-tab"><?php $this->_printLinks(); ?></div>
            <div class="tab-pane fade <?= $this->_getTabClasses('changePassword', false); ?>" id="pills-changePassword" role="tabpanel" aria-labelledby="pills-changePassword-tab"> <?php $this->_printChangePasswordForm(); ?> </div>
            <div class="tab-pane fade <?= $this->_getTabClasses('changeEmail', false); ?>" id="pills-changeEmail" role="tabpanel" aria-labelledby="pills-changeEmail-tab"> <?php $this->_printChangeEmailForm(); ?> </div>
          </div>
          <?php else: ?>
          <?php $this->_printLinks(); ?>
          <?php endif; ?>
        </div>
      </div>

    </main>
  <?php
      $this->_printFooter();
    }

    private function _getTabClasses($tab, $forMenu = true)
    {
      switch ($tab) {
        case 'links':
          if ($_SESSION['firstPasswordIsStillActive'] || $_SESSION['firstEmailIsStillActive'])
            return 'disabled';
          else if ($this->_path === '/')
            return $forMenu ? 'active' : 'show active';
          else
            return '';
        case 'changePassword':
          if (!$_SESSION['firstPasswordIsStillActive'] && $_SESSION['firstEmailIsStillActive'])
            return 'disabled';
          else if ($this->_path === '/changePassword')
            return $forMenu ? 'active' : 'show active';
          else
            return '';
        case 'changeEmail':
          if ($_SESSION['firstPasswordIsStillActive'])
            return 'disabled';
          else if ($this->_path === '/changeEmail')
            return $forMenu ? 'active' : 'show active';
          else
            return '';
      }
    }

    private function _printLinks()
    {
  ?>
    <h4 class="mb-4"><?= $this->_trId("links.title"); ?></h4>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
      <?php
      foreach ($this->_links as $linkName => $linkMeta) : ?>
        <div class="col">
          <div class="card shadow-sm">
            <img class="mb-4 ml-4 mr-4 mt-4" src="<?= $linkMeta['image'] ?>" alt="" height="150">
            </svg>

            <div class="card-body">
              <?php
              if (isset($linkMeta['href'])) {
                echo "<h2><a class=\"link-primary\" href=\"" . $linkMeta['href'] . "\" target=\"_blank\">$linkName</a></h2>";
              } else {
                echo "<h2>$linkName</h2>";
              }
              ?>
              <p class="card-text"><?= $linkMeta['description'] ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php
    }

    private function _printChangePasswordForm()
    {
  ?>
    <p>
      <?= $this->_trId("changePassword.passwordRulesHeader"); ?>
    <ul>
      <?php
      foreach ($this->_translations['passwordRules'] as $passwordRule) {
        echo "<li>" . $passwordRule . "</li>";
      }
      ?>
    </ul>
    </p>
    <form class="row g-3" action="changePassword/submit" method="post">
      <div class="col-12">
        <label for="inputOldPassword" class="form-label"><?= $this->_trId("changePassword.currentPasswordLabel"); ?></label>
        <input name="oldPassword" type="password" class="form-control" id="inputOldPassword">
      </div>
      <div class="col-md-6">
        <label for="inputNewPassword" class="form-label"><?= $this->_trId("changePassword.newPasswordLabel"); ?></label>
        <input name="newPassword" type="password" class="form-control" id="inputNewPassword">
      </div>
      <div class="col-md-6">
        <label for="inputNewPasswordRepeat" class="form-label"><?= $this->_trId("changePassword.repeatNewPasswordLabel"); ?></label>
        <input name="newPasswordRepeat" type="password" class="form-control" id="inputNewPasswordRepeat">
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary"><?= $this->_trId("changePassword.submitLabel"); ?></button>
      </div>
    </form>
  <?php
      $this->_printFooter();
    }

    private function _printChangeEmailForm()
    {
  ?>
    <p>
      <?= $this->_trId("changeEmail.disclaimer"); ?>
    </p>
    <?php if (!$_SESSION['firstEmailIsStillActive']) : ?>
      <p>
      <?= $this->_trId("changeEmail.currentEmailLabel"); ?> <strong><?= $_SESSION['email']; ?></strong>
      </p>
    <?php endif; ?>
    <form action="changeEmail/submit" method="post">
      <div class="mb-3">
        <label for="inputEmail" class="form-label"><?= $this->_trId("global.emailAddress"); ?></label>
        <input name="email" type="email" class="form-control" id="inputEmail">
      </div>
      <button type="submit" class="btn btn-primary"><?= $this->_trId("changeEmail.submitLabel"); ?></button>
    </form>
<?php
      $this->_printFooter();
    }

    // ----------------------------------------
    // - LDAP/authentication helper functions -
    // ----------------------------------------

    private function _bindToLdapAsAdmin()
    {
      $this->_ldapDs = ldap_connect($this->_ldapConfig['host'], $this->_ldapConfig['port']);
      
      if(!$this->_ldapDs) {
        $_SESSION['lastResult'] = 'ldapConnectFailed';
        $this->_redirect('/');
      }

      ldap_set_option($this->_ldapDs, LDAP_OPT_PROTOCOL_VERSION, 3);
      ldap_set_option($this->_ldapDs, LDAP_OPT_REFERRALS, 0);
      ldap_set_option($this->_ldapDs, LDAP_OPT_NETWORK_TIMEOUT, 10);

      if (!$bindResult = ldap_bind($this->_ldapDs, $this->_ldapConfig['binduser'], $this->_ldapConfig['binduserPassword'])) {
        if(ldap_error($this->_ldapDs) === "Can't contact LDAP server")
          $_SESSION['lastResult'] = 'ldapConnectFailed';
        else
          $_SESSION['lastResult'] = 'bindingToLdapAdminFailed';
        $this->_redirect('/');
      }
    }

    private function _isUserAuthenticated()
    {
      return $_SESSION['loggedIn'];
    }

    private function _authenticateUser($username, $password, $deauthenticateOnFailure = true)
    {
      $this->_bindToLdapAsAdmin();
      $username = $this->_sanitizeStringForLdap($username);
      $filter = "(&(" . $this->_ldapConfig['userFilter'] . ")(" . $this->_ldapConfig['usernameField'] . "=$username))";
      if (($search = @ldap_search($this->_ldapDs, $this->_ldapConfig['basedn'], $filter))) {
        $number_returned = ldap_count_entries($this->_ldapDs, $search);

        if ($number_returned === 1) {
          $userEntry = ldap_first_entry($this->_ldapDs, $search);
          $userDn = ldap_get_dn($this->_ldapDs, $userEntry);

          if (ldap_bind($this->_ldapDs, $userDn, $password)) {
            $_SESSION['loggedIn'] = true;
            $_SESSION['userDN'] = $userDn;
            $_SESSION['userName'] = ldap_get_values($this->_ldapDs, $userEntry, $this->_ldapConfig['usernameField'])[0];
            $_SESSION['displayName'] = ldap_get_values($this->_ldapDs, $userEntry, $this->_ldapConfig['displaynameField'])[0];
            $_SESSION['email'] = ldap_get_values($this->_ldapDs, $userEntry, $this->_ldapConfig['emailField'])[0];
            $_SESSION['memberof'] = ldap_get_values($this->_ldapDs, $userEntry, "memberof");
            $_SESSION['lastResult'] = 'loginSuccess';
            $_SESSION['firstPasswordIsStillActive'] = ldap_get_values($this->_ldapDs, $userEntry, "sophomorixFirstPassword")[0] === $password;
            $_SESSION['firstEmailIsStillActive'] = $this->_stringEndsWith(ldap_get_values($this->_ldapDs, $userEntry, $this->_ldapConfig['emailField'])[0], "linuxmuster.lan");
            return true;
          }
        }
      } else {
        $_SESSION['lastResult'] = 'ldapSearchFailed';
        $this->_redirect('/');
      }

      if ($deauthenticateOnFailure)
        $this->_logoutUser();

      $_SESSION['lastResult'] = 'loginFailed';

      return false;
    }

    private function _logoutUser()
    {
      session_unset();
    }

    private function _changeUserPassword($oldPassword, $newPassword)
    {

      if (!$this->_authenticateUser($_SESSION['userName'], $oldPassword, false)) {
        $_SESSION['lastResult'] = 'oldPasswordIsWrong';
        return false;
      }

      $passwordIsValid = true;

      if ($newPassword === $oldPassword) {
        $_SESSION['lastResult'] = 'newPasswordMustNotBeEqualToOldPassword';
        $passwordIsValid = false;
      } else if (strlen($newPassword) < 7) {
        $_SESSION['lastResult'] = 'passwordIsTooShort';
        $passwordIsValid = false;
      } else if (!preg_match("/[0-9]/", $newPassword)) {
        $_SESSION['lastResult'] = 'passwordDoesNotContainANumber';
        $passwordIsValid = false;
      } else if (!preg_match("/[a-zA-Z]/", $newPassword)) {
        $_SESSION['lastResult'] = 'passwordDoesNotContainALetter';
        $passwordIsValid = false;
      } else if (!preg_match("/[A-Z]/", $newPassword)) {
        $_SESSION['lastResult'] = 'passwordDoesNotContainAnUppercaseLetter';
        $passwordIsValid = false;
      } else if (!preg_match("/[a-z]/", $newPassword)) {
        $_SESSION['lastResult'] = 'passwordDoesNotContainALowercaseLetter';
        $passwordIsValid = false;
      }

      if (!$passwordIsValid) {
        return false;
      }

      $newpw64 = $this->_adUnicodePwdValue($newPassword);
      $userDn = $_SESSION['userDN'];

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
        $message = [];
        if ($return_value > 0) {
          //$message[] = "An error occurred while changing the password! Please provide the following info to your system administrator:";
          //$message[] = "STDOUT: $proc_stdout";
          //$message[] = "STDERR: $proc_stderr";
          //$message[] = "EXIT: $return_value";

          $_SESSION['lastResult'] = 'passwordChangeLdapError';
          return false;
        } else {
          $_SESSION['lastResult'] = 'passwordChangedSuccessfully';
          $_SESSION['firstPasswordIsStillActive'] = false;
          return true;
        }
      }
    }

    private function _changeUserEmail($email)
    {
      $this->_bindToLdapAsAdmin();
      $entry = [];
      $entry[$this->_ldapConfig['emailField']] = array($email);
      $result = ldap_mod_replace($this->_ldapDs, $_SESSION['userDN'], $entry);
      if ($result) {
        $_SESSION['firstEmailIsStillActive'] = false;
        $_SESSION['email'] = $email;
        $_SESSION['lastResult'] = 'emailChangedSuccessfully';
        return true;
      } else {
        $_SESSION['lastResult'] = 'emailChangeLdapError';
        return false;
      }
    }

    private function _adUnicodePwdValue($pw)
    {
      $newpw = '';
      $pw = "\"" . $pw . "\"";
      $len = strlen($pw);
      for ($i = 0; $i < $len; $i++)
        $newpw .= "{$pw{$i}}\000";
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

    // ----------------------------
    // - General helper functions -
    // ----------------------------

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

    // ------------------
    // - Language stuff -
    // ------------------

    private function _resultToMessage($result)
    {
      return $this->_translations['results'][$result];
    }

    private function _trId($id)
    {
      $result = $this->_translations;
      foreach (explode(".", $id) as $sub) {
        $result = $result[$sub];
      }
      return $result;
    }
  }

  new MlmUserLandingPage($config);
  die();
