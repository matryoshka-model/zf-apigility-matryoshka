<?php
/**
 * Matryoshka Connected Resource for Apigility
 *
 * @link        https://github.com/matryoshka-model/zf-apigility-matryoshka
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace Matryoshka\Apigility\Model;

use Matryoshka\Model\Criteria\ActiveRecord\AbstractCriteria;
use Matryoshka\Model\Criteria\PaginableCriteriaInterface;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Matryoshka\Model\Object\PrototypeStrategy\PrototypeStrategyAwareInterface;
use Matryoshka\Model\ModelAwareInterface;
use Matryoshka\Model\Object\ObjectManager;

/**
 * Interface MatryoshkaConnectedResourceInterface
 */
interface MatryoshkaConnectedResourceInterface extends ModelAwareInterface, HydratorAwareInterface, PrototypeStrategyAwareInterface
{

    /**
     * @param ObjectManager $objectManager
     */
    public function setObjectManager(ObjectManager $objectManager);

    /**
     * Set the entity class name
     *
     * @param $className
     * @return $this
     */
    public function setEntityClass($className);

    /**
     * Set the entity criteria
     *
     * @param AbstractCriteria $criteria
     * @param $criteria
     * @return $this
     */
    public function setEntityCriteria(AbstractCriteria $criteria);

    /**
     * @param string $className
     */
    public function setCollectionClass($className);

    /**
     * Set the collection (paginable) criteria
     *
     * @param PaginableCriteriaInterface $criteria
     * @param $criteria
     * @return $this
     */
    public function setCollectionCriteria(PaginableCriteriaInterface $criteria);

    /**
     * Set the hydrator of the collection criteria
     *
     * @param HydratorInterface $hydrator
     * @param $hydrator
     * @return $this
     */
    public function setCollectionCriteriaHydrator(HydratorInterface $hydrator);
}
