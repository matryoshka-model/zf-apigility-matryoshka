<?php
/**
 * Matryoshka Connected Resource for Apigility
 *
 * @link        https://github.com/matryoshka-model/zf-apigility-matryoshka
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaTest\Apigility;

use Matryoshka\Apigility\Exception\RuntimeException;
use MatryoshkaTest\Apigility\Asset\FakePluginManager;
use PHPUnit_Framework_TestCase;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager;

/**
 * Class MatryoshkaConnectedResourceAbstractFactoryTest
 */
class MatryoshkaConnectedResourceAbstractFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    public function setUp()
    {
        $this->serviceManager = new ServiceManager\ServiceManager(
            new ServiceManagerConfig(
                [
                    'abstract_factories' => [
                        'Matryoshka\Apigility\MatryoshkaConnectedResourceAbstractFactory',
                    ],
                    'factories' => [
                        'Matryoshka\Model\ModelManager' => 'Matryoshka\Model\Service\ModelManagerFactory',
                        'Matryoshka\Model\Object\ObjectManager' =>
                            'Matryoshka\Model\Object\Service\ObjectManagerFactory',
                        'Matryoshka\Model\ResultSet\PrototypeStrategy\ServiceLocatorStrategy' =>
                            'Matryoshka\Model\ResultSet\PrototypeStrategy\Service\ServiceLocatorStrategyFactory',
                        'HydratorManager' => 'Zend\Mvc\Service\HydratorManagerFactory',
                    ],
                    'invokables' => [
                        'Matryoshka\Model\ResultSet\ArrayObjectResultSet' =>
                            'Matryoshka\Model\ResultSet\ArrayObjectResultSet',
                        'Matryoshka\Model\ResultSet\HydratingResultSet' =>
                            'Matryoshka\Model\ResultSet\HydratingResultSet',
                    ],
                    'services' => [
                        'Matryoshka\Criteria\Test1' => $this->getMockForAbstractClass(
                            'Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria'
                        ),
                        'Matryoshka\Criteria\CollectionTest1' => $this->getMock(
                            'Matryoshka\Model\Criteria\PaginableCriteriaInterface'
                        )
                    ],
                    'shared' => [
                        'Matryoshka\Model\ModelManager' => true,
                        'Matryoshka\Model\Object\ObjectManager' => true,
                        'Matryoshka\Model\ResultSet\ArrayObjectResultSet' => false,
                        'Matryoshka\Model\ResultSet\HydratingResultSet' => false,
                    ],
                ]
            )
        );

        $config = [
            'matryoshka-apigility' => [
                'matryoshka-connected' => [
                    'MatryoshkaApigility\ConnectedResource1' => [
                        'model' => 'Matryoshka\Model',
                        'entity_class' => 'Test',
                        'entity_criteria' => 'Matryoshka\Criteria\Test1',
                        'collection_criteria' => 'Matryoshka\Criteria\CollectionTest1',
                        'hydrator' => 'objectproperty',
                        'collection_criteria_hydrator' => 'objectproperty',
                        'resource_class' => 'Matryoshka\Apigility\Model\MatryoshkaConnectedResource'
                    ],
                    'MatryoshkaApigility\ConnectedResource2' => [],
                    'MatryoshkaApigility\ConnectedResource3' => [
                        'model' => 'Matryoshka\ModelException',
                        'entity_class' => 'Test',
                        'entity_criteria' => 'Matryoshka\Criteria\Test1'
                    ],
                    'MatryoshkaApigility\ConnectedResource4' => [
                        'model' => 'Matryoshka\Model',
                        'entity_class' => 'Test',
                        'hydrator' => 'objectpropertyException',
                    ],
                    'MatryoshkaApigility\ConnectedResource5' => [
                        'model' => 'Matryoshka\Model',
                        'entity_class' => 'Test',
                        'collection_class' => 'Test'
                    ],
                    'MatryoshkaApigility\ConnectedResource6' => [
                        'model' => 'Matryoshka\Model',
                        'entity_class' => 'Test',
                        'hydrator' => 'objectpropertyException',
                        'resource_class' => 'Test'
                    ],
                ]
            ],
        ];

        $this->serviceManager->setService('Config', $config);
        /* @var $mm \Matryoshka\Model\ModelManager */
        $mm = $this->serviceManager->get('Matryoshka\Model\ModelManager');
        $mock = $this->getMockBuilder('Matryoshka\Model\Model')
            ->disableOriginalConstructor()
            ->getMock();
        $mm->setService('Matryoshka\Model', $mock);
    }

    public function testEmptyConfig()
    {
        $this->serviceManager = new ServiceManager\ServiceManager(
            new ServiceManagerConfig(
                [
                    'abstract_factories' => [
                        'Matryoshka\Apigility\MatryoshkaConnectedResourceAbstractFactory',
                    ]
                ]
            )
        );
        $this->assertFalse($this->serviceManager->has('MatryoshkaApigility\ConnectedResource1'));

        $this->serviceManager = new ServiceManager\ServiceManager(
            new ServiceManagerConfig(
                [
                    'abstract_factories' => [
                        'Matryoshka\Apigility\MatryoshkaConnectedResourceAbstractFactory',
                    ]
                ]
            )
        );

        $this->serviceManager->setService('Config', []);
        $this->assertFalse($this->serviceManager->has('MatryoshkaApigility\ConnectedResource1'));
    }

    public function testHasServiceWithoutConfig()
    {
        $this->assertFalse($this->serviceManager->has('MatryoshkaApigility\ConnectedResource2'));
    }

    public function testHasService()
    {
        $this->assertTrue($this->serviceManager->has('MatryoshkaApigility\ConnectedResource1'));
    }

    public function testGetService()
    {
        $this->assertInstanceOf(
            'Matryoshka\Apigility\Model\MatryoshkaConnectedResourceInterface',
            $this->serviceManager->get('MatryoshkaApigility\ConnectedResource1')
        );
    }

    public function testServiceWithAbstractPluginManagerInstance()
    {
        $pm = new FakePluginManager;
        $pm->addAbstractFactory('Matryoshka\Apigility\MatryoshkaConnectedResourceAbstractFactory');
        $pm->setServiceLocator($this->serviceManager);
        $this->assertTrue($pm->has('MatryoshkaApigility\ConnectedResource1'));
        $this->assertInstanceOf(
            'Matryoshka\Apigility\Model\MatryoshkaConnectedResourceInterface',
            $pm->get('MatryoshkaApigility\ConnectedResource1')
        );
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetServiceWithExceptionForWrongModelConfig()
    {
        $this->serviceManager->get('MatryoshkaApigility\ConnectedResource3');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetServiceWithExceptionForWrongHydratorConfig()
    {
        $this->serviceManager->get('MatryoshkaApigility\ConnectedResource4');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetServiceWithExceptionForWrongCollectionClassConfig()
    {
        $this->serviceManager->get('MatryoshkaApigility\ConnectedResource5');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetServiceWithExceptionForWrongResourceClassConfig()
    {
        $this->serviceManager->get('MatryoshkaApigility\ConnectedResource6');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetServiceWithExceptionForWrongObjectEntityConfig()
    {
        $serviceManager = new ServiceManager\ServiceManager(
            new ServiceManagerConfig(
                [
                    'abstract_factories' => [
                        'Matryoshka\Apigility\MatryoshkaConnectedResourceAbstractFactory',
                    ],
                    'factories' => [
                        'Matryoshka\Model\ModelManager' => 'Matryoshka\Model\Service\ModelManagerFactory',
                        'Matryoshka\Model\ResultSet\PrototypeStrategy\ServiceLocatorStrategy' =>
                            'Matryoshka\Model\ResultSet\PrototypeStrategy\Service\ServiceLocatorStrategyFactory',
                        'HydratorManager' => 'Zend\Mvc\Service\HydratorManagerFactory',
                    ],
                    'invokables' => [
                        'Matryoshka\Model\ResultSet\ArrayObjectResultSet' =>
                            'Matryoshka\Model\ResultSet\ArrayObjectResultSet',
                        'Matryoshka\Model\ResultSet\HydratingResultSet' =>
                            'Matryoshka\Model\ResultSet\HydratingResultSet',
                    ],
                    'services' => [
                        'Matryoshka\Criteria\Test1' => $this->getMockForAbstractClass(
                            'Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria'
                        ),
                        'Matryoshka\Criteria\CollectionTest1' => $this->getMock(
                            'Matryoshka\Model\Criteria\PaginableCriteriaInterface'
                        )
                    ],
                    'shared' => [
                        'Matryoshka\Model\ModelManager' => true,
                        'Matryoshka\Model\ResultSet\ArrayObjectResultSet' => false,
                        'Matryoshka\Model\ResultSet\HydratingResultSet' => false,
                    ],
                ]
            )
        );

        $config = [
            'matryoshka-apigility' => [
                'matryoshka-connected' => [
                    'MatryoshkaApigility\ConnectedResource3' => [
                        'model' => 'Matryoshka\Model',
                        'entity_class' => 'TestException',
                        'entity_criteria' => 'Matryoshka\Criteria\Test1',
                        'hydrator' => 'objectproperty',
                    ],
                ]
            ],
        ];

        $serviceManager->setService('Config', $config);
        /* @var $mm \Matryoshka\Model\ModelManager */
        $mm = $serviceManager->get('Matryoshka\Model\ModelManager');
        $mock = $this->getMockBuilder('Matryoshka\Model\Model')
            ->disableOriginalConstructor()
            ->getMock();
        $mm->setService('Matryoshka\Model', $mock);

        $serviceManager->get('MatryoshkaApigility\ConnectedResource3');
    }
}
