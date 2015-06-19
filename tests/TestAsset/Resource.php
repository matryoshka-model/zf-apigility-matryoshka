<?php
/**
 * Matryoshka Connected Resource for Apigility
 *
 * @link        https://github.com/matryoshka-model/zf-apigility-matryoshka
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaTest\Apigility\TestAsset;

use Matryoshka\Apigility\Model\MatryoshkaConnectedResourceInterface;
use Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria;
use Matryoshka\Model\Criteria\PaginableCriteriaInterface;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZF\Rest\AbstractResourceListener;

/**
 * Class Resource
 */
class Resource implements MatryoshkaConnectedResourceInterface
{
    /**
     * Set hydrator
     *
     * @param  HydratorInterface $hydrator
     * @return HydratorAwareInterface
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        // TODO: Implement setHydrator() method.
    }

    /**
     * Retrieve hydrator
     *
     * @return HydratorInterface
     */
    public function getHydrator()
    {
        // TODO: Implement getHydrator() method.
    }

    /**
     * Set the entity_class for the controller config calling this resource
     */
    public function setEntityClass($className)
    {
        // TODO: Implement setEntityClass() method.
    }

    /**
     * @param AbstractCriteria $criteria
     * @return $this
     */
    public function setEntityCriteria(AbstractCriteria $criteria)
    {
        // TODO: Implement setEntityCriteria() method.
    }

    /**
     * @param PaginableCriteriaInterface $criteria
     * @return $this
     */
    public function setCollectionCriteria(PaginableCriteriaInterface $criteria)
    {
        // TODO: Implement setCollectionCriteria() method.
    }

    /**
     * @param HydratorInterface $hydrator
     * @return $this
     */
    public function setCollectionCriteriaHydrator(HydratorInterface $hydrator)
    {
        // TODO: Implement setCollectionCriteriaHydrator() method.
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }
}
