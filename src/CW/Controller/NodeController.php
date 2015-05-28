<?php
/**
 * @file
 *
 * Simple node controller.
 *
 * @addtogroup cwentity
 * @{
 */

namespace CW\Controller;

/**
 * Class NodeController
 * @package CW\Controller
 *
 * Most basic implementation of node controller.
 */
class NodeController extends AbstractEntityController {

  // Entity type.
  const TYPE_NODE = 'node';

  /**
   * {@inheritdoc}
   */
  public static function getClassEntityType() {
    return self::TYPE_NODE;
  }

  /**
   * @return bool
   */
  public function isPublished() {
    return $this->property('status') == NODE_PUBLISHED;
  }

  /**
   * Get the relative path of the content. (Not transformed.)
   * Drupal's url can turn it to the transformed version.
   *
   * @return string
   */
  public function getPath() {
    return 'node/' . $this->getEntityId();
  }

  /**
   * @return mixed
   */
  public function getTitle() {
    return $this->property('title');
  }

  /**
   * @return string
   */
  public function getTitleLinkedToEntity() {
    return l($this->getTitle(), $this->getPath());
  }

  /**
   * @return mixed
   */
  public function getCreatedTimestamp() {
    return $this->property('created');
  }

  /**
   * Render node view.
   *
   * @param string $viewMode
   * @param null $langCode
   * @return string
   */
  public function render($viewMode = 'full', $langCode = NULL) {
    if (!$this->entity()) {
      return NULL;
    }

    $nodeView = node_view($this->entity(), $viewMode, $langCode);
    if (empty($nodeView)) {
      return NULL;
    }

    return render($nodeView);
  }

}

/**
 * @}
 */