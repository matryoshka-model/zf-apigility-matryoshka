<?php
/**
 * Matryoshka Connected Resource for Apigility
 *
 * @link        https://github.com/matryoshka-model/zf-apigility-matryoshka
 * @copyright   Copyright (c) 2015-2016, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaTest\Apigility\Asset;

use Matryoshka\Model\Criteria\IdentityCriteriaInterface;
use Matryoshka\Model\Criteria\ReadableCriteriaInterface;
use Matryoshka\Model\Object\IdentityAwareTrait;
use Matryoshka\Model\ModelStubInterface;

class ReadableIdentityCriteria implements IdentityCriteriaInterface, ReadableCriteriaInterface
{
    use IdentityAwareTrait;
    
    public function apply(ModelStubInterface $model)
    {
        return new \ArrayObject([]);
    }
}