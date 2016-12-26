<?php

namespace Mcenter\Util;

/**
 * Generate random string
 *
 * @author danhtn
 */
class RandomPasswordUtil {

  /**
   * @return type String
   */
  public static function randomPassword() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 8; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

}
