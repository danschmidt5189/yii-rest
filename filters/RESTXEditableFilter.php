<?php
/**
 * RESTEditableFilter.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.*');

/**
 * Maps X-Editable POST parameters to standard Yii POST formats
 *
 * This applies explicitly to HTTP requests and maps X-Editable POSTed data to Yii's
 * standard-practice GET / POST forms, then forwards to another action to handle the request. It is a
 * quick-and-dirty solution to the problem of integrating
 *
 * X-Editable sends data as:
 * <code>
 * POST =>[
 *     name  =>'firstname',
 *     value =>'Daniel',
 *     pk    =>123,
 * ]
 * </code>
 *
 * This maps it to:
 * <code>
 * GET =>[
 *     'id' =>123
 * ]
 * POST =>[
 *     'Customer' =>[
 *         'firstname' =>'Value'
 *     ]
 * ]
 * </code>
 *
 * @package     yii-rest
 * @subpackage  filters
 * @version     0.1
 */
class RESTXEditableFilter extends RESTFilter
{
    /**
     * @var string  name of the GET variable into which the $_POST['pk'] field is mapped
     */
    public $id = 'id';

    /**
     * @var string  name of the key prefixed to the 'name' field POSTed by X-Editable. If X-Editable POSTs
     *              'firstname', it is mapped to $_POST[$prefix]['firstname'].
     */
    public $prefix;

    /**
     * @var string  route to which X-Editable requests are forwarded after filter. Leave empty to not forward
     *              the request.
     */
    public $forward;

    /**
     * @var boolean  whether to end the application after forwarding
     */
    public $exit = true;

    /**
     * @var array  list of action IDs to which this filter should apply. Defaults to NULL, meaning it will
     *             be invoked on all actions.
     */
    public $actions = array();

    /**
     * Reformats and then forwards X-Editable requests to another action
     *
     * @return boolean  true if the request is not forwarded
     */
    public function preFilter($filterChain)
    {
        $controller = $filterChain->controller;
        $action     = $filterChain->action;

        if ($this->getIsXEditableRequest() && $this->isActionMatched($action)) {
            $_GET[$this->id]                      = $_POST['pk'];
            $_POST[$this->prefix][$_POST['name']] = $_POST['value'];
            unset($_POST['pk'], $_POST['name'], $_POST['value']);
            if ($this->forward) {
                $controller->forward($this->forward, $this->exit);
            }
        }
        return true;
    }

    /**
     * Returns whether the filter matches the given action
     *
     * @param CAction $action  the action to match based on ID
     * @return boolean  whether this filter applies to the given action
     */
    public function isActionMatched(CAction $action)
    {
        return empty($this->actions) || false !== array_search($action->id, $this->actions);
    }

    /**
     * Returns whether the current request is an X-Editable request
     *
     * @return boolean  whether the 'name', 'value', and 'pk' POST fields are set
     */
    public function getIsXEditableRequest()
    {
        return isset($_POST['name'], $_POST['value'], $_POST['pk']);
    }
}