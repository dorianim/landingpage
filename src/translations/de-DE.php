<?php

defined('L_EXEC') or die();

// Available placeholders:
// {$config['customization']['organizationName']}
// {$config['customization']['fullOrganizationName']}
// {$config['customization']['supportEmailAddress']}

$translations['home']['hello'] = "Hallo";
$translations['home']['welcomeMessage'] = "Wilkommen bei deinem {$config['customization']['organizationName']}-Account - Ein Login für alles";
$translations['home']['menu']['changePasswordLabel'] = "Passwort ändern";
$translations['home']['menu']['changeEmailLabel'] = "E-Mail-Adresse ändern";
$translations['home']['menu']['generateJitsiLinkLabel'] = "Jitsi Link generieren";
$translations['home']['menu']['logoutLabel'] = "Abmelden";
$translations['home']['menu']['loginLabel'] = "Anmelden";

// These rules are currently hard coded!
$translations['passwordRules'][0] = "Das Passwort muss mindestens 7 Zeichen lang sein.";
$translations['passwordRules'][1] = "Das Passwort muss mindestens eine Zahl oder ein Sonderzeichen enthalten.";
$translations['passwordRules'][2] = "Das Passwort muss mindestens einen Klein- und einen Großbuchstaben enthalten.";

$translations['results']['internalError'] = "Interner Fehler. Falls das Problem bestehen bleibt, kontaktieren sie bitte <a href=\"mailto:{$config['customization']['supportEmailAddress']}?body=Fehlercode: %ERR%; URI: {$_SERVER['REQUEST_URI']}\">{$config['customization']['supportEmailAddress']}</a> (%ERR%)";
$translations['results']['permissionDenied'] = "Zugriff verweigert";
$translations['results']['ldapConnectFailed'] = str_replace("%ERR%", "0000", $translations['results']['internalError']);
$translations['results']['bindingToLdapAdminFailed'] = str_replace("%ERR%", "0001", $translations['results']['internalError']);
$translations['results']['ldapSearchFailed'] = str_replace("%ERR%", "0002", $translations['results']['internalError']);
$translations['results']['noPermissionToAnyPage'] = str_replace("%ERR%", "0005", $translations['results']['internalError']);
$translations['results']['ldapTlsInitializationFailed'] = str_replace("%ERR%", "0006", $translations['results']['internalError']);
$translations['results']['loginSuccess'] = "Erfolgreich angemeldet";
$translations['results']['loginFailed'] = "Ungültige Zugangsdaten";
$translations['results']['loginRequired'] = "Bitte zuerst anmelden!";
$translations['results']['oldPasswordIsWrong'] = "Das aktulle Passwort ist falsch";
$translations['results']['newPasswordMustNotBeEqualToOldPassword'] = "Das neue Passwort darf nicht mit dem Alten übereinstimmen";
$translations['results']['newPasswordAndRepeatDidNotMatch'] = "Die Passwörter stimmen nicht überein";
$translations['results']['passwordIsTooShort'] = $translations['passwordRules'][0];
$translations['results']['passwordDoesNotContainANumberOrSpecialCharacter'] = $translations['passwordRules'][1];
$translations['results']['passwordDoesNotContainALetter'] = $translations['passwordRules'][2];
$translations['results']['passwordDoesNotContainAnUppercaseLetter'] = $translations['passwordRules'][2];
$translations['results']['passwordDoesNotContainALowercaseLetter'] = $translations['passwordRules'][2];
$translations['results']['passwordChangeLdapError'] = str_replace("%ERR%", "0003", $translations['results']['internalError']);
$translations['results']['newPasswordMustNotBeOldPassword'] = "Das neue Passwort darf nicht mit dem Alten übereinstimmen.";
$translations['results']['passwordChangedSuccessfully'] = "Dein Passwort wurde erfolgreich geändert.";
$translations['results']['emailChangeLdapError'] = str_replace("%ERR%", "0004", $translations['results']['internalError']);
$translations['results']['emailChangedSuccessfully'] = "Deine E-Mail-Adresse wurde erfolgreich geändert.";
$translations['results']['invalidEmailError'] = "Die eingegeben E-Mail-Adresse ist ungültig.";
$translations['results']['generateJitsiLinkSuccessfull'] = "Link erfolgreich generiert";
$translations['results']['generateJitsiLinkRoomMustNotBeEmpty'] = "Der Raumname darf nicht leer sein";
$translations['results']['csrfTokenInvalid'] = "Der CSRF-Token ist ungültig";

$translations['notifications']['changeFirstPassword'] = "Schritt 1 von 2: Du nutzt immernoch dein Erstpasswort. Dieses Passwort ist nicht sicher, bitte ändere es jetzt.";
$translations['notifications']['changeFirstEmail'] = "Schritt 2 von 2: Deine E-Mail-Adresse ist noch noch nicht hinterlegt, bitte hinterlege sie jetzt.";

$translations['globals']['title'] = "{$config['customization']['organizationName']} Account";
$translations['globals']['usernameLabel'] = "Benutzername";
$translations['globals']['passwordLabel'] = "Passwort";
$translations['globals']['emailAddress'] = "E-Mail-Adresse";

$translations['login']['title'] = "Bitte anmelden";
$translations['login']['submitLabel'] = "Anmelden";
$translations['login']['footnote'] = "Login für Mitglieder des {$config['customization']['fullOrganizationName']}";

$translations['changePassword']['passwordRulesHeader'] = "Regeln für Passwörter:";
$translations['changePassword']['currentPasswordLabel'] = "Aktuelles Passwort";
$translations['changePassword']['newPasswordLabel'] = "Neues Passwort";
$translations['changePassword']['repeatNewPasswordLabel'] = "Neues Password Wiederholen";
$translations['changePassword']['submitLabel'] = "Passwort ändern";

$translations['changeEmail']['disclaimer'] = "Deine E-Mail-Adresse wird verwendet, um dir Benachrichtigungen über neue Nachrichten in Rocket.Chat und Gerätereservierungen zukommen zu lassen. Sie wird nicht an Dritte weitergegeben.";
$translations['changeEmail']['currentEmailLabel'] = "Deine aktuelle E-Mail-Adresse ist:";
$translations['changeEmail']['submitLabel'] = "E-Mail-Adresse ändern";

$translations['generateJitsiLink']['disclaimer'] = "Generiere einen Jitsi Link für Moderatorenrechte in einem bestimmten Raum";
$translations['generateJitsiLink']['roomLabel'] = "Raumname";
$translations['generateJitsiLink']['submitLabel'] = "Generieren";
$translations['generateJitsiLink']['linkLabel'] = "Hier ist dein Link :)";