<?php
/**
 * HALAttributeCollection class file.
 *
 * @copyright 2013 by iWorldServices, Inc.
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('yii-rest.behaviors.hal.*');

/**
 * Represents a collection of HAL attributes.
 *
 * @package rest
 * @subpackage hal
 **/
class HALAttributeCollection extends CAttributeCollection
{
    /**
     * @var boolean HALAttributeCollection is caseSensitive by default
     **/
    public $caseSensitive = true;
}