<?php
namespace Matryoshka\Apigility\Model;

use Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria;
use Matryoshka\Model\Criteria\PaginableCriteriaInterface;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Interface MatryoshkaConnectedResourceInterface
 */
interface MatryoshkaConnectedResourceInterface extends HydratorAwareInterface
{
    /**
     * Set the entity class name
     *
     * @param $className
     * @return $this
     */
    public function setEntityClass($className);

    /**
     * @param AbstractCriteria $criteria
     * @param $criteria
     * @return $this
     */
    public function setEntityCriteria(AbstractCriteria $criteria);

    /**
     * @param PaginableCriteriaInterface $criteria
     * @param $criteria
     * @return $this
     */
    public function setCollectionCriteria(PaginableCriteriaInterface $criteria);

    /**
     * @param HydratorInterface $hydrator
     * @param $hydrator
     * @return $this
     */
    public function setCollectionCriteriaHydrator(HydratorInterface $hydrator);
}
