<?php
/**
 * HALMetaCollection class file.
 *
 * @copyright 2013 by iWorldServices, Inc.
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('yii-rest.behaviors.hal.*');

/**
 * Represents a collection of HAL _meta properties.
 *
 * @package rest
 * @subpackage hal
 **/
class HALMetaCollection extends CAttributeCollection
{
    /**
     * @var boolean HALMetaCollection is caseSensitive by default
     **/
    public $caseSensitive = true;
}