<?php
/**
 * RESTController class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('ext.yii-rest.*');

/**
 * Base RESTController class
 *
 * This class overrides the default getActionParams() method to return a model instead of a raw data array.
 * The model is stored in [_actionParams] and can be referenced throughout the request cycle. Because CModel
 * implements ArrayAccess, the model's public properties can be used for action-parameter binding like a normal array.
 *
 * @property string $formClassName  name of the form class used to
 * @property CModel $actionParams   the form model containing action parameters
 *
 * @method RESTParams getActionParams()          returns the loaded parameters model
 * @method RESTParams loadActionParams()         loads the parameters model
 * @method string     getActionParamsScenario()  returns the validation scenario for the action parameters
 * @method array      getRawActionParams()       returns the raw action parameters. This returns the attributes
 *                                               that are set into [actionParams] and is analogous to the default
 *                                               [CController::getActionParams()].
 *
 * @package     yii-rest
 * @subpackage  components
 * @version     0.1
 */
abstract class RESTController extends CController
{
    /**
     * @var string  name of the form class used to validate action parameters. If not set,
     *              a name is guessed using the pattern "{ControllerId}Params".
     */
    public $formClassName;

    /**
     * @var CModel  the form model representing action parameters
     */
    private $_actionParams;

    /**
     * Returns the [_actionParams] property
     *
     * If action parameters have not been initialized, they are loaded using [loadActionParams()].
     *
     * @param boolean $reset  whether to force resetting of the action parameters model
     * @return RESTParams  action parameters
     */
    public function getActionParams($reset=false)
    {
        if (null === $this->_actionParams || $reset) {
            $this->_actionParams = $this->loadActionParams();
        }
        return $this->_actionParams;
    }

    /**
     * Loads the action parameters form model
     *
     * @return CModel  the action parameters model for the current request
     */
    public function loadActionParams()
    {
        $formClassName = $this->formClassName ?: ucfirst($this->id).'Params';
        $form = new $formClassName($this->getActionParamsScenario());
        $form->setAttributes($this->getRawActionParams());
        return $form;
    }

    /**
     * Returns the raw action parameters
     *
     * These are used to set attributes of the form model that validates the action parameter.
     *
     * @return array  action parameter attributes. Defaults to GET and POST.
     */
    public function getRawActionParams()
    {
        return $_GET + $_POST;
    }

    /**
     * Returns the validation scenario for the action params form
     *
     * This is used by [loadActionParams()] to set the model scenario when it is instantiated.
     *
     * @return string  scenario name. Defaults to the action id if it is set.
     */
    public function getActionParamsScenario()
    {
        return isset($this->action->id) ? $this->action->id : '';
    }

    public function filters()
    {
        return array(
            // Filters out invalid HTTP methods
            array('RESTVerbsFilter', 'actions' =>array('update'), 'verbs' =>array('PUT', 'PATCH')),
            array('RESTVerbsFilter', 'actions' =>array('create'), 'verbs' =>array('POST')),
            array('RESTVerbsFilter', 'actions' =>array('delete'), 'verbs' =>array('DELETE')),
            // Validates the action parameters
            array('RESTParamsFilter'),
        );
    }
}