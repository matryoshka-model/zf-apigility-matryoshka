<?php
/**
 * Matryoshka Connected Resource for Apigility
 *
 * @link        https://github.com/matryoshka-model/zf-apigility-matryoshka
 * @copyright   Copyright (c) 2015, Ripa Club
 * @license     http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace Matryoshka\Apigility\Exception;

use ZF\Rest\Exception\RuntimeException as ZFRuntimeException;

class RuntimeException extends ZFRuntimeException implements ExceptionInterface
{

}