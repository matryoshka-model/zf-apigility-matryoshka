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
     * Set the entity_class for the controller config calling this resource
     */
    public function setEntityClass($className);

    /**
     * @param AbstractCriteria $criteria
     * @return $this
     */
    public function setEntityCriteria(AbstractCriteria $criteria);

    /**
     * @param PaginableCriteriaInterface $criteria
     * @return $this
     */
    public function setCollectionCriteria(PaginableCriteriaInterface $criteria);

    /**
     * @param HydratorInterface $hydrator
     * @return $this
     */
    public function setCollectionCriteriaHydrator(HydratorInterface $hydrator);


} 