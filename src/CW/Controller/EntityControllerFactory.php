<?php
/**
 * @file
 *
 * Entity controller factory.
 */

namespace CW\Controller;

use CW\Factory\Creator;
use CW\Model\ObjectHandler;
use CW\Util\LocalProcessIdentityMap;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Class EntityControllerFactory
 * @package CW\Controller
 *
 * The purpose of this class to create entity controllers.
 */
class EntityControllerFactory {

  /**
   * Identity map cache.
   *
   * @var LocalProcessIdentityMap
   */
  private $localProcessIdentityMap;

  /**
   * Actual entity controller class to instantiate.
   *
   * @var string
   */
  protected $controllerClass;

  /**
   * Entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * Object loader that takes care of low level data loading.
   *
   * @var ObjectHandler
   */
  private $objectLoader;

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param LocalProcessIdentityMap $localProcessIdentityMap
   *  Identity map cache.
   * @param ObjectHandler $objectLoader
   *  Low level data loader.
   * @param string $controllerClass
   *  Actual entity controller class.
   * @param string $entityType
   *  Entity type.
   * @param LoggerInterface $logger
   */
  public function __construct(LocalProcessIdentityMap $localProcessIdentityMap, ObjectHandler $objectLoader, $controllerClass, $entityType, LoggerInterface $logger) {
    $this->localProcessIdentityMap = $localProcessIdentityMap;

    if (!is_subclass_of($controllerClass, 'CW\Controller\AbstractEntityController')) {
      throw new InvalidArgumentException('Controller class is not subclass of CW\Controller\AbstractEntityController');
    }
    $this->controllerClass = $controllerClass;

    $this->entityType = $entityType;
    $this->objectLoader = $objectLoader;
    $this->logger = $logger;
  }

  /**
   * Factory method.
   *
   * @param mixed $entity_id
   * @return AbstractEntityController
   */
  public function initWithId($entity_id) {
    $controller = NULL;

    $cacheKey = 'entity:' . $this->entityType . ':' . $entity_id;
    if ($this->localProcessIdentityMap->keyExist($cacheKey)) {
      $controller = $this->localProcessIdentityMap->get($cacheKey);
    }
    else {
      $controller = new $this->controllerClass($this->objectLoader, $this->logger, $this->entityType, $entity_id);
      $this->localProcessIdentityMap->add($cacheKey, $controller);
    }

    return $controller;
  }

  public function initWithEntity($entity) {
    list($id,,) = entity_extract_ids($this->entityType, $entity);
    $controller = $this->initWithId($id);
    $controller->setDrupalEntity($entity);
    return $controller;
  }

  public function initNew(Creator $creator) {
    $entity = $creator->create();
    return $this->initWithEntity($entity);
  }

}
