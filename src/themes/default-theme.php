<?php

defined('L_EXEC') or die();

class LandingpageTheme
{

  private $_globalConfig;
  private $_translations;
  private $_links;
  private $_resultLevels;

  public function __construct($config, $links, $translations)
  {
    $this->_globalConfig = $config;
    $this->_translations = $translations;

    $this->_links = $links;

    $this->_resultLevels['loginSuccess'] = "success";
    $this->_resultLevels['loginFailed'] = "danger";
    $this->_resultLevels['ldapConnectFailed'] = "danger";
    $this->_resultLevels['ldapSearchFailed'] = "danger";
    $this->_resultLevels['ldapTlsInitializationFailed'] = "danger";
    $this->_resultLevels['bindingToLdapAdminFailed'] = "danger";
    $this->_resultLevels['loginRequired'] = "warning";
    $this->_resultLevels['oldPasswordIsWrong'] = "danger";
    $this->_resultLevels['newPasswordMustNotBeEqualToOldPassword'] = "danger";
    $this->_resultLevels['newPasswordAndRepeatDidNotMatch'] = "danger";
    $this->_resultLevels['passwordIsTooShort'] = "danger";
    $this->_resultLevels['passwordDoesNotContainANumberOrSpecialCharacter'] = "danger";
    $this->_resultLevels['passwordDoesNotContainALetter'] = "danger";
    $this->_resultLevels['passwordDoesNotContainAnUppercaseLetter'] = "danger";
    $this->_resultLevels['passwordDoesNotContainALowercaseLetter'] = "danger";
    $this->_resultLevels['passwordChangeLdapError'] = "danger";
    $this->_resultLevels['newPasswordMustNotBeOldPassword'] = "danger";
    $this->_resultLevels['passwordChangedSuccessfully'] = 'success';
    $this->_resultLevels['emailChangedSuccessfully'] = 'success';
    $this->_resultLevels['emailChangeLdapError'] = 'danger';
    $this->_resultLevels['invalidEmailError'] = 'danger';
    $this->_resultLevels['permissionDenied'] = 'danger';
    $this->_resultLevels['generateJitsiLinkRoomMustNotBeEmpty'] = 'danger';
    $this->_resultLevels['generateJitsiLinkSuccessfull'] = 'success';
    $this->_resultLevels['csrfTokenInvalid'] = 'danger';
  }

  public function printPage($page)
  {
    switch ($page) {
      case 'login':
        $this->_printLogin();
        break;
      default:
        $this->_printHome($page);
    }
  }

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
        :root {
          --primary_500: 255, 0, 0;
        }

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

        .card-footer {
          width: 100%;
        }

        a:focus {
          outline: none;
        }

        .navbar-light .navbar-nav .nav-link {
          color: #0d6efd;
          cursor: pointer;
        }

        .navbar-light .navbar-nav .nav-link:focus, .navbar-light .navbar-nav .nav-link:hover {
          color: #0a58ca;
        }

        .navbar-light .navbar-nav .nav-link.active, .navbar-light .navbar-nav .show > .nav-link {
          color: #fff;
        }

        .navbar-nav .nav-link {
          padding: .5rem 1rem;
        }

        .navbar > .container-fluid  {
          padding-left: 0px;
          padding-right: 0px;
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
      if ($_SESSION['auth']['firstPasswordIsStillActive'])
        $this->_printAlert($this->_trId('notifications.changeFirstPassword'), "warning", false);
      else if ($_SESSION['auth']['firstEmailIsStillActive'])
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
  <script>
    var forms = document.getElementsByTagName('form')

    for (const form of forms) {
      var formButtons = form.getElementsByTagName("button");
      for (const button of formButtons) {
        if (button.type === "submit") {
          form.addEventListener("submit", () => {
            console.log(button)
            button.innerHTML += ' <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>'
            button.disabled = true
          })
        }
      }

      var formInputs = form.getElementsByTagName("input")
      for (const input of formInputs) {
        form.addEventListener("submit", () => {
          input.readonly = true
        })
      }
    }
  </script>

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
          <?= $this->_csrfFormField() ?>
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

    private function _printHome($page)
    {
      $this->_printHeader();
  ?>
    <main class="bg-light">

      <section class="py-5 text-center bg-white">
        <div class="py-lg-5">
          <div class="col-lg-6 col-md-8 mx-auto">
            <img class="mb-4" src="<?= $this->_globalConfig['mainIcon'] ?>" alt="" height="150">
            <?php if ($this->_globalConfig['loginEnabled']) : ?>
              <h1 class="fw-light"><?= $this->_trId("home.hello"); ?> <?= $_SESSION['auth']['displayName'] ?></h1>
            <?php endif; ?>
            <p class="lead text-muted"><?= $this->_trId("home.welcomeMessage"); ?></p>
          </div>
        </div>
      </section>

      <div class="album">
        <div class="container pt-3 pb-3">
          <?php $this->_printPasswordAndEmailChangeNotification();
          $this->_printResultAlert(); ?>
          <nav class="navbar navbar-expand-lg navbar-light bg-light rounded border-bottom pb-3 pt-0">
              <div class="container-fluid">
                <a id="navbar-brand" class="navbar-brand d-lg-none"><?= $page === "links" ? "":$this->_trId("home.menu." . $page . "Label") ?></a>
                <button id="navbarButton" class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon"></span>
                </button>
                <div id="navbar" class="navbar-collapse collapse">
                  <ul class="navbar-nav nav nav-pills me-auto mb-2 mb-lg-0 mt-2 mt-lg-0" id="pills-tab" role="tablist">
                    <? $this->_printLinkCategoryMenuItems($page); ?>
                  </ul>

                  <ul class="navbar-nav nav nav-pills">
                    <li class="nav-item" role="presentation">
                      <a class="nav-link <?= $this->_getTabClasses('generateJitsiLink', $page); ?>" href="generateJitsiLink"><?= $this->_trId("home.menu.generateJitsiLinkLabel"); ?></a>
                    </li>
                    <li class="nav-item" role="presentation">
                      <a class="nav-link <?= $this->_getTabClasses('changePassword', $page); ?>" href="changePassword"><?= $this->_trId("home.menu.changePasswordLabel"); ?></a>
                    </li>
                    <li class="nav-item pe-lg-3" role="presentation">
                      <a class="nav-link <?= $this->_getTabClasses('changeEmail', $page); ?>" href="changeEmail"><?= $this->_trId("home.menu.changeEmailLabel"); ?></a>
                    </li>
                    <li class="nav-item mt-2 mt-lg-0" role="presentation">
                    <?php if ($_SESSION['permissions']['logout']) : ?>
                      <form action="logout/submit" method="post">
                        <?= $this->_csrfFormField() ?>
                        <button type="submit" class="btn btn-outline-secondary w-100"><?= $this->_trId("home.menu.logoutLabel"); ?></button>
                      </form>
                    <?php elseif ($_SESSION['permissions']['login']) : ?>
                      <a class="btn btn-outline-secondary w-100" href="login"><?= $this->_trId("home.menu.loginLabel"); ?></a>
                    <?php endif; ?>
                    </li>
                  </ul>
                </div>
              </div>
          </nav>
          <div class="tab-content pt-3" id="pills-tabContent">
            <? $this->_printTabContent($page); ?>
          </div>
        </div>
      </div>

    </main>
  <?php
      $this->_printFooter();
    }

    private function _getTabClasses($tab, $page, $forMenu = true)
    {
      if (!$_SESSION['permissions'][$tab])
        return 'd-none';
      else if ($page === $tab && $page != 'links')
        return $forMenu ? 'active' : 'show active';
      else
        return '';
    }

    private function _getActiveLinkCategory() {
      $activeCategory = md5(array_key_first($this->_links));
      if(isset($_GET["category"])) {
        foreach ($this->_links as $categoryName => $categoryMeta) {
          if(md5($categoryName) == $_GET["category"]) {
            $activeCategory = $_GET["category"];
          }
        }
      }
      return $activeCategory;
    }

    private function _printLinkCategoryMenuItems($page) {
      if (!$_SESSION['permissions']['links']) {
        return;
      } 

      $activeCategory = $this->_getActiveLinkCategory();
      
      foreach ($this->_links as $categoryName => $categoryMeta) :
        $categoryId = md5($categoryName); ?>
        <li class="nav-item" role="presentation">
          <a class="nav-link <?= ($activeCategory === $categoryId && $page === "links" ? "active":""); ?>" 
            <?php if ($page === "links"): ?>
            id="pills-<?= $categoryId ?>-tab" 
            data-bs-toggle="pill" 
            role="tab" 
            data-bs-target="#pills-<?= $categoryId ?>"
            aria-controls="pills-<?= $categoryId ?>" 
            aria-selected="true"
            linkCategoryId="<?= $categoryId ?>"
            <?php else: ?>
            href="links?category=<?= $categoryId; ?>" 
            <?php endif; ?>
            ><?= $categoryName; ?></a>
        </li>
      <?php endforeach;
    }

    private function _printTabContent($page) {
      switch($page) {
        case "links": $this->_printLinks(); break;
        case "changePassword": $this->_printChangePasswordForm(); break;
        case "changeEmail": $this->_printChangeEmailForm(); break;
        case "generateJitsiLink": $this->_printGenerateJitsiLinkForm(); break;
      }
    }

    private function _printLinks()
    {
      if (!$_SESSION['permissions']['links']) {
        return;
      } 

      $activeCategory = $this->_getActiveLinkCategory();

      foreach ($this->_links as $categoryName => $categoryMeta) :
        $categoryId = md5($categoryName); ?>
    <div class="tab-pane fade <?= $activeCategory === $categoryId ? "show active":"" ?>" id="pills-<?= $categoryId ?>" role="tabpanel" aria-labelledby="pills-<?= $categoryId ?>-tab">
      <? if (isset($categoryMeta['title'])): ?>
      <h4 class="mb-3"><?= $categoryMeta['title'] ?></h4>
      <? endif; ?>
      
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
        <?php foreach ($categoryMeta['links'] as $linkName => $linkMeta) : ?>
          <div class="col">
            <div class="card shadow-sm h-100">
              <?php if (isset($linkMeta['href'])) : ?>
                <a href=<?= $linkMeta['href'] ?> target="_blank">
                <?php endif; ?>
                <img class="mb-4 ml-4 mr-4 mt-4" src="<?= $linkMeta['image'] ?>" alt="" height="150">
                <h2><?= $linkName ?></h2>
                </img>
                <?php if (isset($linkMeta['href'])) : ?>
                </a>
              <?php endif; ?>

              <div class="card-body d-flex align-items-center">
                <p class="card-text"><?= $linkMeta['description'] ?></p>
              </div>

              <?php if (isset($linkMeta['footer'])) : ?>
                <div class="card-footer">
                  <?= $linkMeta['footer'] ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <script>
    // Change url and navbar-brand text when tab is changed
    var tabEls = document.querySelectorAll('a[data-bs-toggle="pill"]')
    var navbarBrand = document.getElementById('navbar-brand')
    for(tabEl of tabEls) {
      tabEl.addEventListener('shown.bs.tab', function (event) {
        var url = new URL(document.URL);
        url.searchParams.set('category', event.target.getAttribute("linkcategoryid"));
        window.history.pushState(
          "",
          document.title,
          url.toString()
        )
        navbarBrand.innerHTML = event.target.innerHTML
      })
      
      if(tabEl.classList.contains("active")) {
        navbarBrand.innerHTML = tabEl.innerHTML
      }
    }
    </script>
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
        <input name="oldPassword" type="password" class="form-control" id="inputOldPassword" required autofocus>
      </div>
      <div class="col-md-6">
        <label for="inputNewPassword" class="form-label"><?= $this->_trId("changePassword.newPasswordLabel"); ?></label>
        <input name="newPassword" type="password" class="form-control" id="inputNewPassword" required>
      </div>
      <div class="col-md-6">
        <label for="inputNewPasswordRepeat" class="form-label"><?= $this->_trId("changePassword.repeatNewPasswordLabel"); ?></label>
        <input name="newPasswordRepeat" type="password" class="form-control" id="inputNewPasswordRepeat" required>
      </div>
      <?= $this->_csrfFormField() ?>
      <div class="col-12">
        <button type="submit" class="btn btn-primary"><?= $this->_trId("changePassword.submitLabel"); ?></button>
      </div>
    </form>
  <?php
    }

    private function _printChangeEmailForm()
    {
  ?>
    <p>
      <?= $this->_trId("changeEmail.disclaimer"); ?>
    </p>
    <?php if (!$_SESSION['auth']['firstEmailIsStillActive']) : ?>
      <p>
        <?= $this->_trId("changeEmail.currentEmailLabel"); ?> <strong><?= $_SESSION['auth']['email']; ?></strong>
      </p>
    <?php endif; ?>
    <form action="changeEmail/submit" method="post">
      <div class="mb-3">
        <label for="inputEmail" class="form-label"><?= $this->_trId("globals.emailAddress"); ?></label>
        <input name="email" type="email" class="form-control" id="inputEmail" required autofocus>
      </div>
      <?= $this->_csrfFormField() ?>
      <button type="submit" class="btn btn-primary"><?= $this->_trId("changeEmail.submitLabel"); ?></button>
    </form>
  <?php
    }

    private function _printGenerateJitsiLinkForm()
    {
  ?>
    <p>
      <?= $this->_trId("generateJitsiLink.disclaimer"); ?>
    </p>
    <?php if ($_SESSION['lastResult'] === 'generateJitsiLinkSuccessfull') : ?>
      <p><a target="_blank" href="<?= $_SESSION['generateJitsiLinkLink'] ?>"><?= $this->_trId("generateJitsiLink.linkLabel"); ?></a></p>
    <?php endif; ?>
    <form action="generateJitsiLink/submit" method="post">
      <div class="mb-3">
        <label for="inputRoom" class="form-label"><?= $this->_trId("generateJitsiLink.roomLabel"); ?></label>
        <input name="room" type="text" class="form-control" id="inputRoom" required autofocus>
      </div>
      <?= $this->_csrfFormField() ?>
      <button type="submit" class="btn btn-primary"><?= $this->_trId("generateJitsiLink.submitLabel"); ?></button>
    </form>
<?php
    }

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

    private function _csrfFormField() {
      return "<input type=\"hidden\" name=\"csrfToken\" value=\"" . $_SESSION['csrfToken'] . "\" />";
    }
  }

?>