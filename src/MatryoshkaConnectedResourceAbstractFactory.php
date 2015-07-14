<?php
/**
 * Matryoshka Connected Resource for Apigility
 *
 * @link        https://github.com/matryoshka-model/zf-apigility-matryoshka
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Apigility;

use Matryoshka\Apigility\Exception\RuntimeException;
use Matryoshka\Apigility\Model\MatryoshkaConnectedResourceInterface;
use Matryoshka\Model\AbstractModel;
use Matryoshka\Model\Object\ObjectManager;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Class MatryoshkaConnectedResourceAbstractFactory
 */
class MatryoshkaConnectedResourceAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Config
     *
     * @var array
     */
    protected $config;

    /**
     * Config Key
     *
     * @var string
     */
    protected $moduleConfigKey = 'matryoshka-apigility';

    /**
     * Config Key
     *
     * @var string
     */
    protected $configKey = 'matryoshka-connected';

    /**
     * Default model class name
     *
     * @var string
     */
    protected $resourceClass = '\Matryoshka\Apigility\Model\MatryoshkaConnectedResource';

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        $config = $this->getConfig($serviceLocator);
        if (empty($config)) {
            return false;
        }

        return (
            isset($config[$requestedName])
            && is_array($config[$requestedName])
            && !empty($config[$requestedName])
            && isset($config[$requestedName]['model'])
            && is_string($config[$requestedName]['model'])
        );
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return MatryoshkaConnectedResourceInterface
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        $config = $this->getConfig($serviceLocator)[$requestedName];
        $model = $this->getModelServiceFromConfig($config, $serviceLocator, $requestedName);
        $resourceClass = $this->getResourceClassFromConfig($config, $requestedName);

        /* @var $resource MatryoshkaConnectedResourceInterface */
        $resource = new $resourceClass($model);

        // Entity setup
        if (!empty($config['entity_class'])) {
            $resource->setEntityClass($config['entity_class']);
            $resource->setObjectManager($this->getObjectManagerFromConfig($serviceLocator));
        }

        if (!empty($config['entity_criteria'])) {
            $resource->setEntityCriteria($serviceLocator->get($config['entity_criteria']));
        }

        if (!empty($config['hydrator'])) {
            $resource->setHydrator($this->getHydratorByName($serviceLocator, $config['hydrator']));
        }

        if (!empty($config['prototype_strategy'])) {
            $resource->setPrototypeStrategy($serviceLocator->get($config['prototype_strategy']));
        }

        // Collection setup
        $collectionClass = $this->getCollectionFromConfig($config, $requestedName);
        $resource->setCollectionClass($collectionClass);

        if (!empty($config['collection_criteria'])) {
            $resource->setCollectionCriteria($serviceLocator->get($config['collection_criteria']));
        }

        if (!empty($config['collection_criteria_hydrator'])) {
            $resource->setCollectionCriteriaHydrator(
                $this->getHydratorByName(
                    $serviceLocator,
                    $config['collection_criteria_hydrator']
                )
            );
        }

        return $resource;
    }

    /**
     * Retrieve the object manager
     *
     * @param ServiceLocatorInterface $serviceManager
     * @return ObjectManager
     * @throws RuntimeException
     */
    protected function getObjectManagerFromConfig(ServiceLocatorInterface $serviceManager)
    {
        if ($serviceManager->has('Matryoshka\Model\Object\ObjectManager')) {
            return $serviceManager->get('Matryoshka\Model\Object\ObjectManager');
        }

        throw new RuntimeException(
            sprintf(
                'Unable to obtain instance of "%s"',
                'Matryoshka\Model\Object\ObjectManager'
            )
        );
    }

    /**
     * Retrieve the resource class name
     *
     * @param array $config
     * @param $requestedName
     * @throws RuntimeException
     * @return string
     */
    protected function getResourceClassFromConfig(array $config, $requestedName)
    {
        $resourceClass = isset($config['resource_class']) ? $config['resource_class'] : $this->resourceClass;
        if ($resourceClass !== $this->resourceClass &&
            (
                !class_exists($resourceClass) ||
                !is_subclass_of($resourceClass, MatryoshkaConnectedResourceInterface::class)
            )
        ) {
            throw new RuntimeException(
                sprintf(
                    'Unable to create instance for service "%s"; ' .
                    'resource class "%s" does not exist or does not implement "%s"',
                    $requestedName,
                    $resourceClass,
                    MatryoshkaConnectedResourceInterface::class
                )
            );
        }

        return $resourceClass;
    }

    /**
     * Retrieve the model service
     *
     * @param array $config
     * @param ServiceLocatorInterface $services
     * @throws RuntimeException
     * @return AbstractModel
     */
    protected function getModelServiceFromConfig(array $config, ServiceLocatorInterface $services)
    {
        if ($services->get('Matryoshka\Model\ModelManager')->has($config['model'])) {
            return $services->get('Matryoshka\Model\ModelManager')->get($config['model']);
        }

        throw new RuntimeException(
            sprintf(
                'Unable to create instance for service "%s"',
                $config['model']
            )
        );
    }

    /**
     * Retrieve the collection class name
     *
     * @param array $config
     * @param $requestedName
     * @throws RuntimeException
     * @return string
     */
    protected function getCollectionFromConfig(array $config, $requestedName)
    {
        $collection = isset($config['collection_class']) ? $config['collection_class'] : 'Zend\Paginator\Paginator';
        if (!class_exists($collection)) {
            throw new RuntimeException(
                sprintf(
                    'Unable to create instance for service "%s"; collection class "%s" cannot be found',
                    $requestedName,
                    $collection
                )
            );
        }
        return $collection;
    }

    /**
     * Retrieve HydratorInterface object
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @return HydratorInterface
     * @throws RuntimeException
     */
    protected function getHydratorByName(ServiceLocatorInterface $serviceLocator, $name)
    {
        if ($serviceLocator->has('HydratorManager')) {
            $serviceLocator = $serviceLocator->get('HydratorManager');
        }

        return $serviceLocator->get($name);
    }

    /**
     * Get model configuration, if any
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $serviceLocator)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$serviceLocator->has('Config')) {
            $this->config = [];
            return $this->config;
        }

        $config = $serviceLocator->get('Config');
        if (!isset($config[$this->moduleConfigKey])
            || !isset($config[$this->moduleConfigKey][$this->configKey])
            || !is_array($config[$this->moduleConfigKey][$this->configKey])
        ) {
            $this->config = [];
            return $this->config;
        }

        $this->config = $config[$this->moduleConfigKey][$this->configKey];

        return $this->config;
    }
}
