<?php
namespace MatryoshkaTest\Apigility\Model;

use Matryoshka\Apigility\Model\MatryoshkaConnectedResource;
use MatryoshkaTest\Model\Criteria\AbstractCriteriaTest;
use PHPUnit_Framework_TestCase;

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
        $model = $this->getMock('Matryoshka\Model\AbstractModel');
        $objectManager = $this->getMock('Matryoshka\Model\Object\ObjectManager');
        $this->resource = new MatryoshkaConnectedResource($model, $objectManager);
    }

    public function testConstruct()
    {
        $this->assertInstanceOf('Matryoshka\Apigility\Model\MatryoshkaConnectedResourceInterface', $this->resource);
    }

    public function testGetSetEntityCriteria()
    {
        $criteria = $this->getMock('Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria');
        $this->assertSame($this->resource, $this->resource->setEntityCriteria($criteria));
        $this->assertSame($criteria, $this->resource->getEntityCriteria());
    }

    /**
     * @depends testGetSetEntityCriteria
     * @expectedException \RuntimeException
     */
    public function testGetEntityCriteriaException()
    {
        $this->resource->getEntityCriteria();
    }

    public function testGetSetCollectionCriteria()
    {
        $criteria = $this->getMock('Matryoshka\Model\Criteria\PaginableCriteriaInterface');
        $this->assertSame($this->resource, $this->resource->setCollectionCriteria($criteria));
        $this->assertSame($criteria, $this->resource->getCollectionCriteria());
    }

    /**
     * @depends testGetSetCollectionCriteria
     * @expectedException \RuntimeException
     */
    public function testGetCollectionCriteriaException()
    {
        $this->resource->getCollectionCriteria();
    }

    public function testGetSetCollectionCriteriaHydrator()
    {
        $this->assertInstanceOf('Zend\Stdlib\Hydrator\ClassMethods', $this->resource->getCollectionCriteriaHydrator());
        $hydrator = $this->getMock('Zend\Stdlib\Hydrator\HydratorInterface');
        $this->assertSame($this->resource, $this->resource->setCollectionCriteriaHydrator($hydrator));
        $this->assertSame($hydrator, $this->resource->getCollectionCriteriaHydrator());
    }
}
