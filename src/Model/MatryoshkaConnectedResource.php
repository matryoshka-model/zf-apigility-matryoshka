<?php
/**
 * Matryoshka Connected Resource for Apigility
 *
 * @link        https://github.com/matryoshka-model/zf-apigility-matryoshka
 * @copyright   Copyright (c) 2015-2016, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Apigility\Model;

use Matryoshka\Apigility\Exception\RuntimeException;
use Matryoshka\Model\Criteria\PaginableCriteriaInterface;
use Matryoshka\Model\ModelAwareInterface;
use Matryoshka\Model\ModelAwareTrait;
use Matryoshka\Model\ModelInterface;
use Matryoshka\Model\Object\ObjectManager;
use Matryoshka\Model\Object\PrototypeStrategy\PrototypeStrategyAwareTrait;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\HydratorAwareTrait;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use Matryoshka\Model\Criteria\IdentityCriteriaInterface;
use Matryoshka\Model\Criteria\WritableCriteriaInterface;
use Matryoshka\Model\Criteria\DeletableCriteriaInterface;
use Matryoshka\Model\Criteria\ReadableCriteriaInterface;

/**
 * Class MatryoshkaConnectedResource
 *
 */
class MatryoshkaConnectedResource extends AbstractResourceListener implements MatryoshkaConnectedResourceInterface
{
    use ModelAwareTrait;
    use HydratorAwareTrait;
    use PrototypeStrategyAwareTrait;

    /**
     * The collection_class config for the calling controller zf-rest config
     */
    protected $collectionClass = 'Zend\Paginator\Paginator';

    /**
     * @var IdentityCriteriaInterface
     */
    protected $entityCriteria;

    /**
     * @var PaginableCriteriaInterface
     */
    protected $collectionCriteria;

    /**
     * @var HydratorInterface
     */
    protected $collectionCriteriaHydrator;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * {@inheritdoc}
     */
    public function __construct(ModelInterface $model)
    {
        $this->setModel($model);
    }

    /**
     * {@inheritdoc}
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectManager()
    {
        if (!$this->objectManager) {
            throw new RuntimeException('ObjectManager required');
        }
        return $this->objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityCriteria()
    {
        if (!$this->entityCriteria) {
            throw new RuntimeException('Entity criteria required');
        }
        return $this->entityCriteria;
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityCriteria(IdentityCriteriaInterface $criteria)
    {
        $this->entityCriteria = $criteria;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollectionCriteria()
    {
        if (!$this->collectionCriteria) {
            throw new RuntimeException('Collection criteria required');
        }
        return $this->collectionCriteria;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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

        if ($entityClass = $this->getEntityClass()) {
            $object = $this->getObjectManager()->get($entityClass);
        } else {
            $object = $this->getPrototypeStrategy()->createObject($this->model->getObjectPrototype(), $data);
        }

        $this->retrieveHydrator($object)->hydrate($data, $object);
        
        if ($object instanceof ModelAwareInterface) {
            $object->setModel($this->getModel());
        }
        
        $entityCriteria = clone $this->getEntityCriteria();
        if ($entityCriteria instanceof WritableCriteriaInterface) {
            $this->getModel()->save($this->getEntityCriteria(), $object);
            return $object;
        }

        throw new RuntimeException(
            sprintf(
                'Cannot create entity: criteria is not an instance of "%s"',
                WritableCriteriaInterface::class
            ),
            500
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $entityCriteria = clone $this->getEntityCriteria();
        if ($entityCriteria instanceof DeletableCriteriaInterface) {
                $result = $this->getModel()->delete(
                $entityCriteria->setId($id)
            );
            //when $result is null means we have no information about operation completation
            return ($result === null || $result > 0);
        }
        
        throw new RuntimeException(
            sprintf(
                'Cannot delete entity: criteria is not an instance of "%s"',
                DeletableCriteriaInterface::class
            ),
            500
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        $entityCriteria = clone $this->getEntityCriteria();
        if ($entityCriteria instanceof ReadableCriteriaInterface) {
            $object = $this->getModel()->find(
                $entityCriteria->setId($id)
            )->current();

            if (!$object) {
                return new ApiProblem(404, 'Item not found');
            }
            
            return $object;
        }

        throw new RuntimeException(
            sprintf(
                'Cannot fetch entity: criteria is not an instance of "%s"',
                ReadableCriteriaInterface::class
            ),
            500
        );
        
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($params = [])
    {
        $params = (array) $params;
        if (empty($params)) {
            // when no params and no collectionCriteria have been set
            // then the model default criteria is used
            $criteria = $this->collectionCriteria ? clone $this->collectionCriteria : null;
        } else {
            // when params are present, collectionCriteria is mandatory
            // because we need to hydrate the criteria with current params
            $criteria = clone $this->getCollectionCriteria();
            $hydrator = $this->getCollectionCriteriaHydrator();
            $hydrator->hydrate($params, $criteria);
        }
        
        $paginatorAdapter = $this->getModel()->getPaginatorAdapter($criteria);
        $collectionClassName = $this->getCollectionClass();
        return new $collectionClassName($paginatorAdapter);
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, $data)
    {
        // Does entity exist?
        $oldObject = $this->fetch($id);
        if ($oldObject instanceof ApiProblem) {
            return $oldObject;
        }

        // $data could not cointain all fields so we merge new data on top the old one
        $data = array_merge(
            $this->retrieveHydrator($oldObject)->extract($oldObject),
            $this->retrieveData($data)
        );

        // Get a new object instance in order to ensure that saving operation
        // can work properly even if the entity class is different
        if ($entityClass = $this->getEntityClass()) {
            $object = $this->getObjectManager()->get($entityClass);
        } else {
            $object = $this->getPrototypeStrategy()->createObject(
                $this->getModel()->getObjectPrototype(),
                $data
            );
        }

        // Finally, hydrate and save the new object, replacing the old one
        $this->retrieveHydrator($object)->hydrate($data, $object);

        if ($object instanceof ModelAwareInterface) {
            $object->setModel($this->getModel());
        }
        
        $entityCriteria = clone $this->getEntityCriteria();
        if ($entityCriteria instanceof WritableCriteriaInterface) {
            $this->getModel()->save($entityCriteria->setId($id), $object);
            return $object;
        }
        
        throw new RuntimeException(
            sprintf(
                'Cannot update entity: criteria is not an instance of "%s"',
                WritableCriteriaInterface::class
            ),
            500
        );
    }

    /**
     * {@inheritdoc}
     */
    public function patch($id, $data)
    {
        // TODO: a partial update could be applied if a specific criteria is available
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
     * @param object $object
     * @throws RuntimeException
     * @return HydratorInterface
     */
    protected function retrieveHydrator($object)
    {
        $hydrator = $this->getHydrator();
        if (!$hydrator) {
            if ($object instanceof HydratorAwareInterface && $object->getHydrator()) {
                $hydrator = $object->getHydrator();
            } else {
                throw new RuntimeException('Cannot get a hydrator');
            }
        }
        return $hydrator;
    }


}
