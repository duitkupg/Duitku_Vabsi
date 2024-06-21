<?php

class Duitku_Config {

  public static $serverKey;
  public static $apiVersion = 2;
  public static $isProduction = false;  
  public static $isSanitized = true;

  const SANDBOX_BASE_URL = 'https://sandbox.duitku.com/webapi';
  const PRODUCTION_BASE_URL = 'https://passport.duitku.com/webapi';

  public static function getBaseUrl()
  {
    return Duitku_Config::$isProduction ?
        Duitku_Config::PRODUCTION_BASE_URL : Duitku_Config::SANDBOX_BASE_URL;
  }
}