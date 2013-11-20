<?php
/**
 * RESTParamFilter.php class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.*');

/**
 * Validates the action parameters using a form model
 *
 * @property string   $form           class name of the form model used to validate action parameters
 * @property array    $actions        list of action IDs to which this filter applies
 * @property callable $errorCallback  callback invoked if the form is invalid
 * @property integer  $statusCode     exception status code
 * @property string   $message        exception message
 * @property integer  $code           exception code
 *
 * @method void    badRequest()       handles the case of an invalid form model
 * @method boolean isActionMatched()  returns whether a given action should trigger this filter
 * @method CModel  loadForm()         loads the form model for the controller/action
 *
 * @package     yii-rest
 * @subpackage  filters
 * @version     0.1
 */
class RESTParamsFilter extends RESTFilter
{
    /**
     * @var string  name of the form class used to validate action parameters. If not set, this will fall
     *              back to the controller's getActionParams() result. If that does not return a form model,
     *              a fatal error will be triggered.
     */
    public $form;

    /**
     * @var array  list of action IDs to which this filter applies. Empty means it applies to all actions.
     */
    public $actions;

    /**
     * @var string  validation scenario for the form model. If empty, defaults to the action id.
     */
    public $scenario;

    /**
     * @var callable  callback invoked if the action parameters are invalid. Returning true from this callback
     *                will allow the filterchain to continue running. This is invoked with the action parameter
     *                form as its argument.
     */
    public $errorCallback;

    /**
     * @var integer  exception HTTP status code
     */
    public $statusCode = 400;

    /**
     * @var string  exception message
     */
    public $message;

    /**
     * @var integer  exception code
     */
    public $code = 0;

    /**
     * Validates action parameters using a form model
     *
     * If the parameters are invalid, invokes the [errorCallback] if it is set and/or throws an exception.
     *
     * @param CFilterChain $filterChain  the filter chain
     */
    public function preFilter($filterChain)
    {
        $controller = $filterChain->controller;
        $action = $filterChain->action;

        if ($this->isActionMatched($action)) {
            $form = $this->loadForm($controller, $action);
            if (!$form->validate()) {
                return $this->badRequest($form);
            }
        }
        return true;
    }

    /**
     * Loads the form used to validate action parameters
     *
     * @param CController $controller  the controller
     * @param CAction     $action      the action
     */
    public function loadForm($controller, $action)
    {
        $params = $controller->getActionParams();
        if ($params instanceof CModel) {
            $form = $params;
        } else {
            $formClass = $this->form;
            $form = new $formClass();
        }
        $form->setScenario($this->scenario ?: $action->id);
        $form->setAttributes($params);
        return $form;
    }

    /**
     * Handles an invalid form
     *
     * @param  RESTParams $form  the invalid form
     * @throws RESTParamsException  if the [errorCallback] is not set or does not return true
     */
    public function badRequest($form)
    {
        if ($this->errorCallback && call_user_func_array($this->errorCallback, array($form))) {
            return true;
        }
        throw new RESTParamsException($form, $this->statusCode, $this->message, $this->code);
    }

    /**
     * Returns whether this filter applies to a given action
     *
     * @param  CAction  $action  the action to test
     * @return boolean  whether the action should trigger this filter
     */
    public function isActionMatched(CAction $action)
    {
        return empty($this->actions) ?: false !== array_search($action->id, $this->actions);
    }
}