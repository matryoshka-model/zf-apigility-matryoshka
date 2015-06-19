<?php
/**
 * Matryoshka Connected Resource for Apigility
 *
 * @link        https://github.com/matryoshka-model/zf-apigility-matryoshka
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Apigility\Model;

use Matryoshka\Apigility\Exception\RuntimeException;
use Matryoshka\Model\AbstractModel;
use Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria;
use Matryoshka\Model\Criteria\PaginableCriteriaInterface;
use Matryoshka\Model\ModelAwareInterface;
use Matryoshka\Model\Object\ActiveRecord\ActiveRecordInterface;
use Matryoshka\Model\Object\ObjectManager;
use Matryoshka\Model\ResultSet\PrototypeStrategy\PrototypeStrategyInterface;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\HydratorAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

/**
 * Class MatryoshkaConnectedResource
 *
 */
class MatryoshkaConnectedResource extends AbstractResourceListener implements MatryoshkaConnectedResourceInterface
{
    use HydratorAwareTrait;

    /**
     * @var AbstractCriteria
     */
    protected $entityCriteria;

    /**
     * @var PrototypeStrategyInterface
     */
    protected $prototypeStrategy;

    /**
     * @var PaginableCriteriaInterface
     */
    protected $collectionCriteria;

    /**
     * @var HydratorInterface
     */
    protected $collectionCriteriaHydrator;

    /**
     * @var AbstractModel
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Ctor
     *
     * @param AbstractModel $model
     * @param ObjectManager $objectManager
     * @param string $collectionClass
     */
    public function __construct(
        AbstractModel $model,
        ObjectManager $objectManager,
        $collectionClass = 'Zend\Paginator\Paginator'
    ) {
        $this->model = $model;
        $this->objectManager = $objectManager;
        $this->setCollectionClass($collectionClass);
    }

    /**
     * @return AbstractCriteria
     */
    public function getEntityCriteria()
    {
        if (!$this->entityCriteria) {
            throw new RuntimeException('Entity criteria required');
        }
        return $this->entityCriteria;
    }

    /**
     * @param AbstractCriteria $criteria
     * @return $this
     */
    public function setEntityCriteria(AbstractCriteria $criteria)
    {
        $this->entityCriteria = $criteria;
        return $this;
    }

    /**
     * @param PrototypeStrategyInterface $strategy
     * @return $this
     */
    public function setPrototypeStrategy(PrototypeStrategyInterface $strategy)
    {
        $this->prototypeStrategy = $strategy;
        return $this;
    }

    /**
     * @return PrototypeStrategyInterface
     */
    public function getPrototypeStrategy()
    {
        return $this->prototypeStrategy;
    }

    /**
     * @return PaginableCriteriaInterface
     */
    public function getCollectionCriteria()
    {
        if (!$this->collectionCriteria) {
            throw new RuntimeException('Collection criteria required');
        }
        return $this->collectionCriteria;
    }

    /**
     * @param PaginableCriteriaInterface $criteria
     * @return $this
     */
    public function setCollectionCriteria(PaginableCriteriaInterface $criteria)
    {
        $this->collectionCriteria = $criteria;
        return $this;
    }

    /**
     * @return HydratorInterface
     */
    public function getCollectionCriteriaHydrator()
    {
        if (!$this->collectionCriteriaHydrator) {
            $this->collectionCriteriaHydrator = new ClassMethods();
        }

        return $this->collectionCriteriaHydrator;
    }

    /**
     * @param HydratorInterface $hydrator
     * @return $this
     */
    public function setCollectionCriteriaHydrator(HydratorInterface $hydrator)
    {
        $this->collectionCriteriaHydrator = $hydrator;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function create($data)
    {
        $data = $this->retrieveData($data);

        if ($prototypeStrategy = $this->getPrototypeStrategy()) {
            $object = $prototypeStrategy->createObject($this->model->getObjectPrototype(), $data);
        } elseif ($entityClass = $this->getEntityClass()) {
            $object = $this->objectManager->get($entityClass);
        } else {
            $object = $this->model->create();
        }

        $this->hydrateObject($data, $object);

        if ($object instanceof ActiveRecordInterface) {
            if ($object instanceof ModelAwareInterface) {
                $object->setModel($this->model);
            }
            $object->save();
            return $object;
        }

        throw new RuntimeException(
            sprintf(
                'Misconfigured connected resource: the object is not an instance of "%s"',
                'Matryoshka\Model\Object\ActiveRecord\ActiveRecordInterface'
            ),
            500
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $result = $this->model->delete(
            $this->getEntityCriteria()->setId($id)
        );
        //when $result is null means we have no information about operation completation
        return ($result === null || $result > 0);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        $object = $this->model->find(
            $this->getEntityCriteria()->setId($id)
        )->current();

        if (!$object) {
            return new ApiProblem(404, 'Item not found');
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($params = [])
    {
        // when no params and no collectionCriteria have been set
        // then the model default criteria is used
        $criteria = $this->collectionCriteria;
        $params = (array) $params;
        if (!empty($params)) {
            // when params are present, collectionCriteria is mandatory
            // because we need to hydrate the criteria with current params
            $criteria = $this->getCollectionCriteria();
            $hydrator = $this->getCollectionCriteriaHydrator();
            $hydrator->hydrate($params, $criteria);
        }

        $paginatorAdapter = $this->model->getPaginatorAdapter($criteria);
        $collectionClassName = $this->getCollectionClass();
        return new $collectionClassName($paginatorAdapter);
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, $data)
    {
        $data = $this->retrieveData($data);

        $object = $this->fetch($id);
        if ($object instanceof ApiProblem) {
            return $object;
        }

        $this->hydrateObject($data, $object);

        if ($object instanceof ModelAwareInterface) {
            $object->setModel($this->model);
        }

        $object->save();
        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function patch($id, $data)
    {
        // TODO: a partial update should be applied
        return $this->update($id, $data);
    }

    /**
     * Retrieve data
     *
     * Retrieve data from composed input filter, if any; if none, cast the data
     * passed to the method to an array.
     *
     * @param mixed $data
     * @return array
     */
    protected function retrieveData($data)
    {
        $filter = $this->getInputFilter();
        if (null !== $filter) {
            return $filter->getValues();
        }
        return (array) $data;
    }

    /**
     * @param array $data
     * @param object $object
     * @throws \RuntimeException
     */
    protected function hydrateObject(array $data, $object)
    {
        $hydrator = $this->getHydrator();
        if (!$hydrator) {
            if ($object instanceof HydratorAwareInterface && $object->getHydrator()) {
                $hydrator = $object->getHydrator();
            } else {
                throw new RuntimeException('Cannot get a hydrator');
            }
        }
        $hydrator->hydrate($data, $object);
    }
}
