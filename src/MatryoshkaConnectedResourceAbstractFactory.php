<?php
namespace Matryoshka\Apigility;

use Matryoshka\Model\AbstractModel;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
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
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        $config = $this->getConfig($serviceLocator)[$requestedName];
        $model = $this->getModelServiceFromConfig($config, $serviceLocator, $requestedName);
        $objectManager = $this->getObjectManagerFromConfig($serviceLocator);
        $collectionClass = $this->getCollectionFromConfig($config, $requestedName);

        $resourceClass = $this->getResourceClassFromConfig($config, $requestedName);

        /* @var $resource \Matryoshka\Apigility\Model\MatryoshkaConnectedResource */
        $resource = new $resourceClass($model, $objectManager, $collectionClass);

        if (isset($config['entity_class'])) {
            $resource->setEntityClass($config['entity_class']);
        }

        if (isset($config['entity_criteria'])) {
            $resource->setEntityCriteria($serviceLocator->get($config['entity_criteria']));
        }

        if (isset($config['collection_criteria'])) {
            $resource->setCollectionCriteria($serviceLocator->get($config['collection_criteria']));
        }

        if (isset($config['collection_criteria_hydrator'])) {
            $resource->setCollectionCriteriaHydrator($this->getHydratorByName(
                $serviceLocator,
                $config['collection_criteria_hydrator'])
            );
        }

        if ($resource instanceof HydratorAwareInterface && isset($config['hydrator'])) {
            $resource->setHydrator($this->getHydratorByName($serviceLocator, $config['hydrator']));
        }

        return $resource;
    }

    /**
     * @param ServiceLocatorInterface $services
     */
    protected function getObjectManagerFromConfig(ServiceLocatorInterface $services)
    {
        if ($services->has('Matryoshka\Model\Object\ObjectManager')) {
            return $services->get('Matryoshka\Model\Object\ObjectManager');
        }
        throw new ServiceNotCreatedException(
            sprintf(
                'Unable to obtain instance of "%s"',
                'Matryoshka\Model\Object\ObjectManager'
            )
        );
    }

    /**
     * @param array $config
     * @param $requestedName
     * @return string
     * @throws \Zend\ServiceManager\Exception\ServiceNotCreatedException
     */
    protected function getResourceClassFromConfig(array $config, $requestedName)
    {
        $resourceClass = isset($config['resource_class']) ? $config['resource_class'] : $this->resourceClass;
        if ($resourceClass !== $this->resourceClass
            && (
                !class_exists($resourceClass)
                || !is_subclass_of($resourceClass, 'Matryoshka\Apigility\Model\MatryoshkaConnectedResourceInterface')
            )
        ) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'Unable to create instance for service "%s"; '
                    . 'resource class "%s" cannot be found or does not extend "%s"',
                    $requestedName,
                    $resourceClass,
                    $this->resourceClass
                )
            );
        }
        return $resourceClass;
    }

    /**
     * @param array $config
     * @param ServiceLocatorInterface $services
     * @return AbstractModel
     */
    protected function getModelServiceFromConfig(array $config, ServiceLocatorInterface $services, $requestedName)
    {
        if ($services->get('Matryoshka\Model\ModelManager')->has($config['model'])) {
            return $services->get('Matryoshka\Model\ModelManager')->get($config['model']);
        }
        throw new ServiceNotCreatedException(
            sprintf(
                'Unable to create instance for service "%s"',
                $config['model']
            )
        );
    }

    protected function getCollectionFromConfig(array $config, $requestedName)
    {
        $collection = isset($config['collection_class']) ? $config['collection_class'] : 'Zend\Paginator\Paginator';
        if (!class_exists($collection)) {
            throw new ServiceNotCreatedException(
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
     * Retrieve HydratorInterface object from config
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @return HydratorInterface
     * @throws Exception\RuntimeException
     */
    protected function getHydratorByName(ServiceLocatorInterface $serviceLocator, $name)
    {
        if ($serviceLocator->has('HydratorManager')) {
            $serviceLocator = $serviceLocator->get('HydratorManager');
        }

        if (!$serviceLocator->has($name)) {
            throw new \RuntimeException(
                sprintf(
                    'Instance %s not config in the Hydrator Manager',
                    $name
                )
            );
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
