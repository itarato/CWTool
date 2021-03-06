<?php
/**
 * @file
 *
 * Entity controller factory.
 */

namespace CW\Factory;

use CW\Model\EntityHandler;
use CW\Util\LocalProcessIdentityMap;
use CW\Util\LoggerObject;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

/**
 * Class EntityControllerFactory
 * @package CW\Controller
 *
 * The purpose of this class to create entity controllers and keep them cached
 * in the identity map container.
 * @see http://martinfowler.com/eaaCatalog/identityMap.html
 *
 * The factory does not care about the entity type or bundle - in case you need
 * type validation you have to add it to the subclass.
 */
class EntityControllerFactory extends LoggerObject implements EntityControllerFactoryInterface {

  // Base entity controller class.
  const ABSTRACT_ENTITY_CONTROLLER_CLASS = 'CW\Controller\AbstractEntityController';

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
   * @var EntityHandler
   */
  private $objectHandler;

  /**
   * Constructor.
   *
   * @param LocalProcessIdentityMap $localProcessIdentityMap
   *  Identity map cache.
   * @param EntityHandler $objectLoader
   *  Low level data loader.
   * @param string $controllerClass
   *  Actual entity controller class.
   * @param string $entityType
   *  Entity type.
   * @param LoggerInterface $logger
   */
  public function __construct(LocalProcessIdentityMap $localProcessIdentityMap, EntityHandler $objectLoader, $controllerClass, $entityType, LoggerInterface $logger) {
    parent::__construct($logger);

    $this->localProcessIdentityMap = $localProcessIdentityMap;

    if (!is_subclass_of($controllerClass, self::ABSTRACT_ENTITY_CONTROLLER_CLASS)) {
      throw new InvalidArgumentException('Controller class is not subclass of ' . self::ABSTRACT_ENTITY_CONTROLLER_CLASS);
    }
    $this->controllerClass = $controllerClass;

    $this->entityType = $entityType;
    $this->objectHandler = $objectLoader;
  }

  /**
   * {@inheritdoc}
   */
  public function initWithId($entity_id = NULL, $cacheKey = NULL) {
    $controller = NULL;

    // Sometimes ID is unavailable, so cacheKey can be provided explicitly.
    // Otherwise it generates an automatic cache key.
    if ($cacheKey === NULL) {
      $isEntityIDMissing = strlen($entity_id) === 0;
      if ($isEntityIDMissing) {
        throw new InvalidArgumentException('Missing entity id and cache key.');
      }
      $cacheKey = 'entity:' . $this->entityType . ':' . $entity_id;
    }

    if ($this->localProcessIdentityMap->keyExist($cacheKey)) {
      $controller = $this->localProcessIdentityMap->get($cacheKey);
    }
    else {
      $controller = new $this->controllerClass($this->logger, $this->objectHandler, $this->entityType, $entity_id);
      $this->localProcessIdentityMap->add($cacheKey, $controller);
    }

    return $controller;
  }

  /**
   * {@inheritdoc}
   */
  public function initWithEntity($entity) {
    list($id,,) = entity_extract_ids($this->entityType, $entity);

    // Entity can be new - without and ID - cache key is important so we use the object hash PHP provides.
    if (strlen($id) == 0) {
      $cache_key = spl_object_hash($entity);
    }
    else {
      $cache_key = NULL;
    }

    $controller = $this->initWithId($id, $cache_key);
    $controller->setEntity($entity);
    return $controller;
  }

  /**
   * {@inheritdoc}
   */
  public function initNew(Creator $creator) {
    $entity = $creator->create();
    return $this->initWithEntity($entity);
  }

}
