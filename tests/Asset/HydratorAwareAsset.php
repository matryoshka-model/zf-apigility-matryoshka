<?php
namespace MatryoshkaTest\Apigility\Asset;

use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\HydratorAwareTrait;

class HydratorAwareAsset implements HydratorAwareInterface
{
    use HydratorAwareTrait;
} 