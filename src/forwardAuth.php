<?php

defined('L_EXEC') or die();

class LandingpageForwardAuth
{
  public function usernameFromServer($server)
  {
    $username = trim($server['HTTP_X_FORWARDED_USERNAME'] ?? '');
    return $username !== '' ? $username : null;
  }
}
