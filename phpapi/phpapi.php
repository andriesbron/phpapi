<?php

class phpapi
{
  public static function isonline($host, $port)
  {
    $online=False;
    $waitTimeoutInSeconds=1;
    if ($fp=fsockopen(gethostbyname($url), $port, $errCode, $errStr, $waitTimeoutInSeconds)){
      $online=True;
    }
    fclose($fp);
    return $online;
  }
}
