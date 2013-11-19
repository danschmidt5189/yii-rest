<?php
/**
 * RESTRoutesBehavior class file.
 *
 * @copyright 2013 by iWorldServices, Inc.
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('yii-rest.behaviors.rest-routes.*');

/**
 * Adds methods allowing a model to register action routes for itself.
 *
 * @package rest
 * @subpackage rest-routes
 */
class RESTRoutesBehavior extends CActiveRecordBehavior
{
    /**
     * @var array the raw action route configurations
     */
    public $actions;

    /**
     * @var RESTRoutesCollection routes indexed by action name. Keys are action names
     * and can be anything. Values are arrays specifying the route, where the
     * first array entry is the controller/action route and remaining entries
     * are key =>value parameter pairs. Parameter values can containg placeholders
     * using the Twig pattern "{{ attr }}", which will be replaced when fetching
     * the route using [Html::arrayValues()].
     */
    private $_routes;

    /**
     * Gets [_routes]
     * @return RESTRoutesCollection
     */
    public function getRoutes($refresh=false)
    {
        if (null === $this->_routes || $refresh) {
            $this->_routes = new RESTRoutesCollection($this->getOwner(), (array)$this->actions);
        }
        return $this->_routes;
    }
}