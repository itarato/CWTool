<?php
/**
 * @file
 *
 * Simple node controller.
 */

namespace CW\Controller;

use stdClass;
use CW\Params\NodeCreationParams;

/**
 * Class NodeController
 * @package CW\Controller
 *
 * Most basic implementation of node controller.
 */
class NodeController extends AbstractEntityController {

  public static function getClassEntityType() {
    return 'node';
  }

}
