<?php
/**
 * Matryoshka Connected Resource for Apigility
 *
 * @link        https://github.com/matryoshka-model/zf-apigility-matryoshka
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */
namespace MatryoshkaTest\Apigility\Asset;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Class FakePluginManager
 */
class FakePluginManager extends AbstractPluginManager
{
    /**
     * {@inheritdoc}
     */
    public function validatePlugin($plugin)
    {
        return true;
    }
}
