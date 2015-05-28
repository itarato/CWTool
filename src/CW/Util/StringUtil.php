<?php
/**
 * @file
 *
 * String util.
 */

namespace CW\Util;

/**
 * Class StringUtil
 * @package CW\Util
 */
class StringUtil {

  /**
   * @param string $string
   * @return mixed
   */
  public static function snakeCase($string) {
    return preg_replace('/[^a-zA-Z0-9]/', '_', $string);
  }

  /**
   * Check that a string ends with another string.
   *
   * @param string $haystack
   * @param string $needle
   * @return bool
   */
  public static function stringEndsWith($haystack, $needle) {
    $offset = drupal_strlen($haystack) - drupal_strlen($needle);
    return strpos($haystack, $needle, $offset) !== FALSE;
  }
}
