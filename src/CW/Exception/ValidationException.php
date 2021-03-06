<?php
/**
 * @file
 *
 * Validation exception.
 */

namespace CW\Exception;

use Exception;

/**
 * Class ValidationException
 * @package CW\Exception
 */
class ValidationException extends \Exception {

  /**
   * @var string
   */
  private $formElementName;

  /**
   * @param string $message
   * @param string $formElementName
   * @param int $code
   * @param \Exception $previous
   */
  public function __construct($message = "", $formElementName = "", $code = 0, Exception $previous = NULL) {
    parent::__construct($message, $code, $previous);
    $this->formElementName = $formElementName;
  }

  /**
   * @return string
   */
  public function getFormElementName() {
    return $this->formElementName;
  }

}
