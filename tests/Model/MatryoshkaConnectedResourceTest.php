<?php
/**
 * Matryoshka Connected Resource for Apigility
 *
 * @link        https://github.com/matryoshka-model/zf-apigility-matryoshka
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaTest\Apigility\Model;

use Matryoshka\Apigility\Model\MatryoshkaConnectedResource;
use Matryoshka\Model\ModelAwareInterface;
use Matryoshka\Model\Object\ActiveRecord\ActiveRecordInterface;
use MatryoshkaTest\Apigility\Asset\HydratorAwareAsset;
use PHPUnit_Framework_TestCase;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods;

/**
 * Class MatryoshkaConnectedResourceTest
 */
class MatryoshkaConnectedResourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MatryoshkaConnectedResource
     */
    protected $resource;

    public function setUp()
    {
        /** @var $model \Matryoshka\Model\ModelInterface */
        $model = $this->getMock('Matryoshka\Model\AbstractModel');
        $this->resource = new MatryoshkaConnectedResource($model);
    }

    public function testConstruct()
    {
        $this->assertInstanceOf('Matryoshka\Apigility\Model\MatryoshkaConnectedResourceInterface', $this->resource);
    }

    /**
     * @expectedException \Matryoshka\Apigility\Exception\RuntimeException
     */
    public function testGetObjectManagerException()
    {
        $this->resource->getObjectManager();
    }

    /**
     * @depends testGetObjectManagerException
     */
    public function testGetSetObjectManager()
    {
        $objectManager = $this->getMock('Matryoshka\Model\Object\ObjectManager');
        $this->resource->setObjectManager($objectManager);
        $this->assertSame($objectManager, $this->resource->getObjectManager());
    }

    public function testGetSetEntityCriteria()
    {
        $criteria = $this->getMock('Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria');
        $this->assertSame($this->resource, $this->resource->setEntityCriteria($criteria));
        $this->assertSame($criteria, $this->resource->getEntityCriteria());
    }

    /**
     * @depends testGetSetEntityCriteria
     * @expectedException \Matryoshka\Apigility\Exception\RuntimeException
     */
    public function testGetEntityCriteriaException()
    {
        $this->resource->getEntityCriteria();
    }

    public function testGetSetCollectionCriteria()
    {
        $this->assertNull($this->resource->getCollectionCriteria()); // test default
        $criteria = $this->getMock('Matryoshka\Model\Criteria\PaginableCriteriaInterface');
        $this->assertSame($this->resource, $this->resource->setCollectionCriteria($criteria));
        $this->assertSame($criteria, $this->resource->getCollectionCriteria());
    }


    public function testGetSetCollectionCriteriaHydrator()
    {
        $this->assertInstanceOf('Zend\Stdlib\Hydrator\ClassMethods', $this->resource->getCollectionCriteriaHydrator());
        $hydrator = $this->getMock('Zend\Stdlib\Hydrator\HydratorInterface');
        $this->assertSame($this->resource, $this->resource->setCollectionCriteriaHydrator($hydrator));
        $this->assertSame($hydrator, $this->resource->getCollectionCriteriaHydrator());
    }

    /**
     * @expectedException \Matryoshka\Apigility\Exception\RuntimeException
     */
    public function testHydrateObjectException()
    {
        $reflection = new \ReflectionClass(get_class($this->resource));
        $method = $reflection->getMethod('hydrateObject');
        $method->setAccessible(true);

        $method->invokeArgs($this->resource, [[], $this->resource]);
    }

    /**
     * @depends testHydrateObjectException
     */
    public function testHydrateObject()
    {
        $reflection = new \ReflectionClass(get_class($this->resource));
        $method = $reflection->getMethod('hydrateObject');
        $method->setAccessible(true);
        $hydratorAware = new HydratorAwareAsset();
        $hydratorAware->setHydrator(new ClassMethods());

        $this->assertNull($method->invokeArgs($this->resource, [[], $hydratorAware]));
    }

    public function testRetrieveDataFromInputFilter()
    {
        $reflectionClass = new \ReflectionClass(get_class($this->resource));

        $property = $reflectionClass->getProperty("inputFilter");
        $property->setAccessible(true);
        $property->setValue($this->resource, new InputFilter());

        $method = $reflectionClass->getMethod('retrieveData');
        $method->setAccessible(true);

        $stdClass = new \stdClass();

        $this->assertEmpty($method->invokeArgs($this->resource, [$stdClass]));
    }

    public function testRetrieveData()
    {
        $reflection = new \ReflectionClass(get_class($this->resource));
        $method = $reflection->getMethod('retrieveData');
        $method->setAccessible(true);
        $stdClass = new \stdClass();

        $this->assertEmpty($method->invokeArgs($this->resource, [$stdClass]));
    }


    public function testFetchApiProblem()
    {
        $criteria = $this->getMock('Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria');
        $criteria->method('setId')
            ->willReturn($criteria);

        $model = $this->resource->getModel();
        $model->method('find')
            ->willReturn($this->getMock('Matryoshka\Model\ResultSet\HydratingResultSet'));

        $this->resource->setEntityCriteria($criteria);
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblem', $this->resource->fetch('test'));
    }

    public function testFetch()
    {
        $criteria = $this->getMock('Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria');
        $criteria->method('setId')
            ->willReturn($criteria);

        $obj = new \stdClass();

        $resultSet = $this->getMock('Matryoshka\Model\ResultSet\HydratingResultSet');
        $resultSet->method('current')
            ->willReturn($obj);

        $model = $this->resource->getModel();
        $model->method('find')
            ->willReturn($resultSet);

        $this->resource->setEntityCriteria($criteria);
        $this->assertSame($obj, $this->resource->fetch('test'));
    }

    public function testFetchAll()
    {
        $criteria = $criteria = $this->getMock('Matryoshka\Model\Criteria\PaginableCriteriaInterface');

        $model = $this->resource->getModel();
        $model->method('getPaginatorAdapter')
            ->willReturn($this->getMock('Zend\Paginator\Adapter\AdapterInterface'));

        $this->resource->setCollectionCriteria($criteria);

        $this->assertInstanceOf('Traversable', $this->resource->fetchAll(['test']));
    }

    public function testDelete()
    {
        $model = $this->resource->getModel();
        $model->method('delete')
            ->willReturn(1);

        $criteria = $this->getMock('Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria');
        $criteria->method('setId')
            ->willReturn($criteria);

        $this->resource->setEntityCriteria($criteria);
        $this->assertTrue($this->resource->delete('test'));
    }

    public function testUpdateApiProblem()
    {
        $criteria = $this->getMock('Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria');
        $criteria->method('setId')
            ->willReturn($criteria);

        $model = $this->resource->getModel();
        $model->method('find')
            ->willReturn($this->getMock('Matryoshka\Model\ResultSet\HydratingResultSet'));

        $this->resource->setEntityCriteria($criteria);
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblem', $this->resource->update('test', []));
    }

    public function testUpdate()
    {
        $criteria = $this->getMock('Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria');
        $criteria->method('setId')
            ->willReturn($criteria);

        $obj = $this->getMock('Matryoshka\Model\Object\ActiveRecord\AbstractActiveRecord');

        $resultSet = $this->getMock('Matryoshka\Model\ResultSet\HydratingResultSet');
        $resultSet->method('current')
            ->willReturn($obj);

        $model = $this->resource->getModel();
        $model->method('find')
            ->willReturn($resultSet);

        $this->resource->setHydrator(new ClassMethods());
        $this->resource->setEntityCriteria($criteria);
        $this->assertSame($obj, $this->resource->update($obj, []));
    }

    public function testPatch()
    {
        $criteria = $this->getMock('Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria');
        $criteria->method('setId')
            ->willReturn($criteria);

        $obj = $this->getMock('Matryoshka\Model\Object\ActiveRecord\AbstractActiveRecord');

        $resultSet = $this->getMock('Matryoshka\Model\ResultSet\HydratingResultSet');
        $resultSet->method('current')
            ->willReturn($obj);

        $model = $this->resource->getModel();
        $model->method('find')
            ->willReturn($resultSet);

        $this->resource->setHydrator(new ClassMethods());
        $this->resource->setEntityCriteria($criteria);
        $this->assertSame($obj, $this->resource->patch($obj, []));
    }

    /**
     * @expectedException \Matryoshka\Apigility\Exception\RuntimeException
     */
    public function testCreateShouldThrowRuntimeExceptionWhenCannotCreateAnActiveRecordInterfaceObject()
    {
        $prototypeStratey = $this->getMock('Matryoshka\Model\Object\PrototypeStrategy\PrototypeStrategyInterface');
        $prototypeStratey->method('createObject')->willReturn(new \stdClass);

        $this->resource->setHydrator(new ClassMethods);
        $this->resource->setPrototypeStrategy($prototypeStratey);
        $this->resource->create([]);
    }

    public function testCreate()
    {
        $object = $this->getMock('\Matryoshka\Model\Object\ActiveRecord\ActiveRecordInterface');

        $prototypeStratey = $this->getMock('Matryoshka\Model\Object\PrototypeStrategy\PrototypeStrategyInterface');
        $prototypeStratey->method('createObject')->willReturn($object);

        $this->resource->setHydrator(new ClassMethods);
        $this->resource->setPrototypeStrategy($prototypeStratey);
        $result = $this->resource->create([]);

        $this->assertInstanceOf(
            ActiveRecordInterface::class,
            $result
        );

        $this->assertSame($object, $result);
    }

    public function testCreateWithEntityClass()
    {
        $object = $this->getMock('\Matryoshka\Model\Object\ActiveRecord\ActiveRecordInterface');

        $objectManager = $this->getMock('Matryoshka\Model\Object\ObjectManager');
        $objectManager->method('get')->willReturn($object);

        $this->resource->setObjectManager($objectManager);
        $this->resource->setEntityClass('TestEntityClass');
        $this->resource->setHydrator(new ClassMethods);

        $result = $this->resource->create([]);

        $this->assertInstanceOf(
            ActiveRecordInterface::class,
            $result
        );

        $this->assertSame($object, $result);
    }

    public function testCreateModelAwareInterfaceObject()
    {
        $object = $this->getMock('\Matryoshka\Model\Object\ActiveRecord\AbstractActiveRecord');
        $prototypeStratey = $this->getMock('Matryoshka\Model\Object\PrototypeStrategy\PrototypeStrategyInterface');
        $prototypeStratey->method('createObject')->willReturn($object);

        $this->resource->setHydrator(new ClassMethods);
        $this->resource->setPrototypeStrategy($prototypeStratey);

        $object->expects($this->at(0))->method('setModel')->with($this->resource->getModel());

        $result = $this->resource->create([]);

        $this->assertInstanceOf(
            ActiveRecordInterface::class,
            $result
        );

        $this->assertInstanceOf(
            ModelAwareInterface::class,
            $result
        );

        $this->assertSame($object, $result);
    }
}
