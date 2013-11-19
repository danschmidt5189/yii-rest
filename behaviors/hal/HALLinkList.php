<?php
/**
 * HALLinkList class file.
 *
 * @copyright 2013 by iWorldServices, Inc.
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('yii-rest.behaviors.hal.*');

/**
 * Represents a list of HAL objects
 *
 * @package rest
 * @subpackage hal
 */
class HALLinkList extends CTypedMap
{
    /**
     * Creates the link list.
     * This method overrides the parent implementation to only allow adding Link objects.
     **/
    public function __construct()
    {
        parent::__construct('HALLink');
    }

    /**
     * Returns the array representation of the HAL list.
     * This method overrides the parent implementation to call toArray on all HAL objects in the list.
     * @return array link indexed by their rel values
     **/
    public function toArray()
    {
        return array_filter(array_map(function ($link) {
            return $link->toArray();
        }, parent::toArray()));
    }
}