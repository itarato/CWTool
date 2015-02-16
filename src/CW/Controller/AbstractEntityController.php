<?php
/**
 * @file
 *
 * Entity controller abstraction.
 */

namespace CW\Controller;

use CW\Model\EntityModel;
use EntityDrupalWrapper;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractEntityController
 * @package CW\Controller
 *
 * Abstraction for entity controller. Contains the model and should be extended
 * for content specific behaviors.
 */
abstract class AbstractEntityController {

  /**
   * The model.
   *
   * @var EntityModel
   */
  protected $entityModel;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param EntityModel $entityModel
   *  The model object.
   */
  public function __construct(EntityModel $entityModel, LoggerInterface $logger) {
    $this->entityModel = $entityModel;
    $this->logger = $logger;
  }

  /**
   * Get the entity model.
   *
   * @return EntityModel
   */
  public function getEntityModel() {
    return $this->entityModel;
  }

  /**
   * Get the entity metadata wrapper object.
   *
   * @return EntityDrupalWrapper
   */
  public function metadata() {
    return $this->getEntityModel()->getEntityMetadataWrapper();
  }

  /**
   * Get the lowest level of data of the object (mostly Drupal - unless defined otherwise).
   *
   * @return object
   */
  public function data() {
    return $this->getEntityModel()->getEntityData();
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return '[' . get_class($this) . ', ' . $this->getEntityModel() . ']@' . spl_object_hash($this);
  }

}
