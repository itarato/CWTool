<?php
/**
 * @file
 *
 * Abstract theme processor.
 */

namespace CW\Processor;

use CW\Exception\ThemeException;
use itarato\VarCheck\VC;

/**
 * Class AbstractThemeProcessor
 * @package CW\Processor
 *
 * Responsibility is to handle pre/post process hooks. Add missing and alter
 * existing variables.
 */
abstract class AbstractThemeProcessor {

  /**
   * @var array
   */
  protected $vars;

  /**
   * @param array $vars
   */
  protected function __construct(&$vars) {
    $this->vars = &$vars;
    if ($this->isApplicable()) {
      $this->execute();
    }
  }

  /**
   * @param array $vars
   * @return static
   */
  public static function process(&$vars) {
    return new static($vars);
  }

  /**
   * Reference to the template variable array.
   *
   * @return array
   */
  public function &getVars() {
    return $this->vars;
  }

  /**
   * @param string $key
   * @return mixed
   */
  public function getVar($key) {
    return !$this->hasVar($key) ? NULL : $this->vars[$key];
  }

  /**
   * Access template variables through VarCheck's convenient format.
   *
   * @return VC
   */
  public function getWrappedVar() {
    return VC::make($this->vars);
  }

  /**
   * @param string $key
   * @param mixed $value
   */
  public function setVar($key, $value) {
    $this->vars[$key] = $value;
  }

  /**
   * @param string $key
   * @param mixed $value
   * @throws ThemeException
   */
  public function appendVar($key, $value) {
    if (!is_array($this->vars[$key])) {
      if (isset($this->vars[$key])) {
        throw new ThemeException('Non array to array conversion at: ' . $key);
      }
      $this->vars[$key] = array();
    }
    $this->vars[$key][] = $value;
  }

  /**
   * @param string $key
   * @return bool
   */
  public function hasVar($key) {
    return isset($this->vars[$key]);
  }

  /**
   * @return mixed
   */
  abstract public function execute();

  /**
   * @return bool
   */
  abstract public function isApplicable();

}
