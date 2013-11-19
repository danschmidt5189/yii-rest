<?php
/**
 * RESTSource class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.*');

/**
 * Represents sources for client-provided REST data
 *
 * @package     yii-rest
 * @subpackage  components
 */
class RESTSource
{
    const ANY    = 'ANY';
    const GET    = 'GET';
    const POST   = 'POST';
    const DELETE = 'DELETE';
}