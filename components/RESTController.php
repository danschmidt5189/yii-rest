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
 * The main purpose of this class is to override the behavior of `getActionParams()` to use a form model
 * rather than raw parameter data. The form model can be accessed like an array (i.e. implements ArrayAccess),
 * but can also be validated using a filter.
 *
 * @property RESTParams $actionParams  the form model containing action parameters
 *
 * @method RESTParams getActionParams()          returns the action parameter model
 * @method RESTParams loadRESTParams()         loads the default form model
 * @method string     getActionParamsScenario()  returns the validation scenario for the action parameters
 * @method array      getRawActionParams()       returns the raw action parameters
 *
 * @package     yii-rest
 * @subpackage  components
 */
class RESTController extends Controller
{
    /**
     * @var string  name of the adaptor class. If empty, '{ControllerId}Adaptor' is used.
     */
    public $restAdaptorClassName;

    /**
     * @var string  name of the params class. If empty, '{ControllerId}Params' is used.
     */
    public $restParamsClass;

    /**
     * @var RESTAdaptor  the facade model for this controller. This maps client requests to the attribute
     *                   format expected by the [_actionParams] model.
     */
    private $_restAdaptor;

    /**
     * @var RESTParams  the form model representing action parameters
     */
    private $_actionParams;

    /**
     * Returns the [_restAdaptor] property
     *
     * @return RESTAdaptor  facade mapper
     */
    public function getRESTAdaptor()
    {
        if (null === $this->_restAdaptor) {
            $this->_restAdaptor = $this->loadRESTAdaptor();
        }
        return $this->_restAdaptor;
    }

    /**
     * Returns the facade component for this controller
     *
     * The default implementation returns a facade with the class name "{ControllerID}Facade"
     * and configures it to pass all GET and POST data without modification.
     *
     * @return RESTAdaptor  the facade
     */
    public function loadRESTAdaptor()
    {
        $facadeClass = $this->restAdaptorClassName ?: $this->id.'Adaptor';
        $facade = new $facadeClass(array_map(
            function ($name) { return array(RESTSource::GET, $name); },
            array_keys($_GET + $_POST)
        ));
        return $facade;
    }

    /**
     * Returns the [_actionParams] property
     *
     * @return RESTParams  action parameters
     */
    public function getActionParams()
    {
        if (null === $this->_actionParams) {
            $this->_actionParams = $this->loadRESTParams();
        }
        return $this->_actionParams;
    }

    /**
     * Loads the action parameters
     *
     * This is used by [getModel()] to load the [_model] property if it is null. The default implementation
     * creates a form with the class name '{ControllerId}Params', sets its scenario to the id of the current action,
     * and sets its attributes to $_GET and $_POST.
     *
     * @return RESTParams  the action parameters model for the current request
     */
    public function loadRESTParams()
    {
        $formClassName = ucfirst($this->id).'Params';
        $form = new $formClassName($this->getActionParamsScenario());
        $form->setAttributes($this->restAdaptor->getRawActionParams());
        return $form;
    }

    /**
     * Returns the scenario for the action params model
     *
     * Defaults to the current action id if it is set, otherwise empty.
     *
     * @return string  scenario name
     */
    public function getActionParamsScenario()
    {
        return isset($this->action->id) ? $this->action->id : '';
    }
}