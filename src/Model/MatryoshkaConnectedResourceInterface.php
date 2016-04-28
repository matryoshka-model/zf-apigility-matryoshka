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
use Matryoshka\Model\ModelInterface;
use Matryoshka\Model\Object\ObjectManager;
use Matryoshka\Model\Object\PrototypeStrategy\PrototypeStrategyAwareInterface;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Matryoshka\Model\Criteria\IdentityCriteriaInterface;

/**
 * Interface MatryoshkaConnectedResourceInterface
 */
interface MatryoshkaConnectedResourceInterface extends
    ModelAwareInterface,
    HydratorAwareInterface,
    PrototypeStrategyAwareInterface
{
    /**
     * @param ModelInterface $model
     */
    public function __construct(ModelInterface $model);

    /**
     * Set the object manager instance
     *
     * @param ObjectManager $objectManager
     * @return $this
     */
    public function setObjectManager(ObjectManager $objectManager);

    /**
     * Get the object manager instance
     *
     * @return ObjectManager
     * @throws RuntimeException
     */
    public function getObjectManager();

    /**
     * Set the entity class name
     *
     * @param string $className
     * @return $this
     */
    public function setEntityClass($className);

    /**
     * @return string
     */
    public function getEntityClass();

    /**
     * Set the entity criteria
     *
     * @param IdentityCriteriaInterface $criteria
     * @param $criteria
     * @return $this
     */
    public function setEntityCriteria(IdentityCriteriaInterface $criteria);

    /**
     * @return AbstractCriteria
     */
    public function getEntityCriteria();

    /**
     * @param string $className
     * @return $this
     */
    public function setCollectionClass($className);

    /**
     * @return string
     */
    public function getCollectionClass();

    /**
     * Set the collection (paginable) criteria
     *
     * @param PaginableCriteriaInterface $criteria
     * @param $criteria
     * @return $this
     */
    public function setCollectionCriteria(PaginableCriteriaInterface $criteria);

    /**
     * Get the collection (paginable) criteria
     *
     * @return PaginableCriteriaInterface
     */
    public function getCollectionCriteria();

    /**
     * Set the hydrator of the collection criteria
     *
     * @param HydratorInterface $hydrator
     * @param $hydrator
     * @return $this
     */
    public function setCollectionCriteriaHydrator(HydratorInterface $hydrator);
}
