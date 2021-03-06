<?php
/**
 * @file
 *
 * Entity container test.
 */

use CW\Controller\AbstractEntityController;
use CW\Factory\EntityControllerFactory;
use CW\Test\TestCase;
use CW\Util\LocalProcessIdentityMap;

/**
 * Class CWToolEntityModelTest
 */
class EntityControllerFactoryTest extends TestCase {

  protected $entityType;

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $objectHandlerMock;

  /**
   * @var LocalProcessIdentityMap
   */
  protected $localProcessIdentityMap;

  /**
   * @var EntityControllerFactory
   */
  protected $entityControllerFactory;

  /**
   * @var PHPUnit_Framework_MockObject_MockObject
   */
  protected $loggerMock;

  public function setUp() {
    parent::setUp();

    $this->entityType = self::randomString();
    $this->localProcessIdentityMap = new LocalProcessIdentityMap();
    $this->objectHandlerMock = $this->getMockBuilder('CW\Model\DrupalEntityHandler')->getMock();
    $this->loggerMock = $this->getMockBuilder('Psr\Log\AbstractLogger')->getMock();
    $this->entityControllerFactory = new EntityControllerFactory(
      $this->localProcessIdentityMap,
      $this->objectHandlerMock,
      'EntityControllerFactoryTest_BasicEntityController',
      $this->entityType,
      $this->loggerMock
    );
    $this->entityControllerFactory;
  }

  public function testEntityInstantiation() {
    $entityId = self::randomInt();
    $entity = (object) array();
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadSingleEntity')
      ->with($this->equalTo($this->entityType), $this->equalTo($entityId))
      ->willReturn($entity);
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('loadMetadata')
      ->with($this->equalTo($this->entityType), $this->equalTo($entity))
      ->willReturn($entity);
    $this->objectHandlerMock
      ->expects($this->once())
      ->method('save')
      ->with()
      ->willReturn($entity);

    $controller = $this->entityControllerFactory->initWithId($entityId);
    $this->assertEquals($controller->getEntityId(), $entityId);
    $this->assertEquals($controller->getEntityType(), $this->entityType);

    $entityStored = $controller->entity();
    $metadata = $controller->metadata();
    $controller->save();

    $dataReload = $controller->entity();
    $metadataReload = $controller->metadata();
    $this->assertEquals($entityStored, $dataReload);
    $this->assertEquals($metadata, $metadataReload);
  }

  public function testSameObjectInitialization() {
    $id = self::randomInt();
    $result_a = $this->entityControllerFactory->initWithId($id);
    $result_b = $this->entityControllerFactory->initWithId($id);
    $this->assertEquals($result_a, $result_b);
  }

  public function testWithInvalidControllerClass() {
    $mapMock = $this->getMockBuilder('CW\Util\LocalProcessIdentityMap')->getMock();
    $objectHandlerMock = $this->getMockBuilder('CW\Model\DrupalEntityHandler')->getMock();
    $entity_type = self::randomString();

    $this->setExpectedException('\InvalidArgumentException');

    new EntityControllerFactory($mapMock, $objectHandlerMock, 'EntityControllerFactoryTest_FakeEntityController', $entity_type, $this->loggerMock);
  }

  public function testInvalidInitializationWithoutIDOrCacheKey() {
    $this->setExpectedException('InvalidArgumentException');
    $this->entityControllerFactory->initWithId(NULL);
  }

}

class EntityControllerFactoryTest_FakeEntityController { }

class EntityControllerFactoryTest_BasicEntityController extends AbstractEntityController { }
