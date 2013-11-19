<?php
/**
 * RESTRoutesCollection class file.
 *
 * @copyright 2013 by iWorldServices, Inc.
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('application.components.Html');
Yii::import('yii-rest.behaviors.rest-routes.*');

/**
 * Represents a collection of model action routes.
 *
 * @package rest
 * @subpackage rest-routes
 */
class RESTRoutesCollection extends CAttributeCollection
{
    /**
     * @var CModel the model for these routes. The model's attribute values
     * are used to fill in placeholder values when fetching routes.
     */
    private $_model;

    /**
     * @var callable callable used to replace placeholder values in routes.
     * It is passed the model instance and route array and should return an array.
     * If not set, defaults to Html::arrayValues().
     */
    private $_placeholderCallback;

    /**
     * Overrides the parent implementation to inject the model dependency.
     */
    public function __construct(CModel $model, $data=null, $readOnly=false)
    {
        $this->_model = $model;
        parent::__construct($data, $readOnly);
    }

    /**
     * Returns the route for the given key.
     * @param string $key route name
     * @return array route with placeholders replaced by model attribute values
     * @see Html::values()
     */
    public function itemAt($key)
    {
        $route = parent::itemAt($key);
        if (null === $route) {
            return $route;
        }
        return $this->replacePlaceholders($route);
    }

    /**
     * Adds a named route to the collection.
     * The 0th element in `$route` should be the route name. If it is not set,
     * then `$routeName` is used as the route.
     * @param string $routeName route name
     * @param mixed $route the route. This can be a string url or an array. If
     * an array, the 0th element should be the route and the remaining elements
     * key =>value parameter pairs. If the 0th element is not set, it is set
     * as `$routeName`.
     */
    public function add($routeName, $route)
    {
        // Passing just a route name.
        if (is_numeric($routeName)) {
            $routeName = $route;
        }
        // Convert raw urls to an array
        if (is_string($route)) {
            $route = array($route);
        }
        // Set the route name as the route if it isn't set
        if (!isset($route[0])) {
            $route = array($routeName) + $route;
        }
        // Set the route.
        parent::add($routeName, $route);
    }

    /**
     * Replaces placeholders when fetching the map as an array
     * @return array routes array
     */
    public function toArray()
    {
        $routes = parent::toArray();
        return array_map(function ($route) {
            return $this->replacePlaceholders($route);
        }, $routes);
    }

    /**
     * Replaces placeholder values
     */
    public function replacePlaceholders(array $route)
    {
        return call_user_func_array($this->placeholderCallback, array($this->_model, $route));
    }

    /**
     * Sets the [_placeholderCallback] property.
     */
    public function setPlaceholderCallback($value)
    {
        $this->_placeholderCallback = $value;
    }

    /**
     * Returns the [_placeholderCallback] property.
     */
    public function getPlaceholderCallback()
    {
        if (null === $this->_placeholderCallback) {
            $this->_placeholderCallback = array('Html', 'arrayValues');
        }
        return $this->_placeholderCallback;
    }

    /**
     * Returns the [_model] property
     * @return CModel
     */
    public function getModel()
    {
        return $this->_model;
    }
}