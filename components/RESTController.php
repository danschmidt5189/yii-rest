<?php
/**
 * RESTController class file.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

Yii::import('yii-rest.components.*');
Yii::import('yii-rest.filters.*');
Yii::import('yii-rest.actions.*');
// Import your own base controller
Yii::import('application.components.Controller');

/**
 * Base RESTController class
 *
 * The main purpose of this class is to override the behavior of `getActionParams()` to use a form model
 * rather than raw parameter data. The form model can be accessed like an array (i.e. implements ArrayAccess),
 * but has the added benefit that you can validate it.
 *
 * @property string      $restAdaptorClassName  name of the adaptor class to use. Defaults to "{ControllerID}Adaptor".
 * @property string      $restParamsClassName   name of the params class to use. Defaults to "{ControllerID}Params".
 *
 * @property RESTParams  $_restParams   the form model containing action parameters
 * @property RESTAdaptor $_restAdaptor  the adaptor that maps client request data to $_restParams attributes
 *
 * @method RESTParams getRESTAdaptor()                returns the controller adaptor
 * @method RESTParams loadRESTAdaptor()               loads the controller adaptor
 * @method RESTParams getActionParams()               overrides the default implementation to return the [_restParams] model
 * @method RESTParams getRESTParams()                 returns the action parameter *model* instead of raw data array
 * @method RESTParams loadRESTParams()                loads the form model
 * @method string     getDefaultRESTParamsScenario()  returns the validation scenario for [_restParams]
 *
 * @package     yii-rest
 * @subpackage  components
 */
class RESTController extends Controller
{
    /**
     * @var string  the default action
     */
    public $defaultAction = 'list';

    /**
     * @var string  name of the adaptor class. If empty, '{ControllerId}Adaptor' is used.
     */
    public $restAdaptorClassName;

    /**
     * @var string  name of the params class. If empty, '{ControllerId}Params' is used.
     */
    public $restParamsClassName;

    /**
     * @var RESTAdaptor  the facade model for this controller. This maps client requests to the attribute
     *                   format expected by the [_restParams] model.
     */
    private $_restAdaptor;

    /**
     * @var RESTParams  the form model representing action parameters
     */
    private $_restParams;

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
     * Returns the adaptor for this controller
     *
     * The default implementation returns a adaptor with the class name "{ControllerID}Adaptor".
     *
     * @return RESTAdaptor  the facade
     */
    public function loadRESTAdaptor()
    {
        $adaptorClassName = $this->restAdaptorClassName ?: $this->guessClassPrefix().'Adaptor';
        $adaptor = new $adaptorClassName();
        return $adaptor;
    }

    /**
     * Overrides the parent implementation to return the loaded [_restParams] property
     *
     * @return CFormModel  loaded [_restParams] value
     */
    public function getActionParams()
    {
        return $this->getRESTParams();
    }

    /**
     * Returns the [_restParams] property
     *
     * @return RESTParams  action parameters
     */
    public function getRESTParams()
    {
        if (null === $this->_restParams) {
            $this->_restParams = $this->loadRESTParams();
        }
        return $this->_restParams;
    }

    /**
     * Loads the rest params model
     *
     * The model is instantiated using the class [restParamsClassName], if it is set, or the name
     * "{ControllerId}Params". Its scenario is set to the id of the current action and its attributes
     * are set using the raw attributes parsed by [_restAdaptor].
     *
     * @return RESTParams  the action parameters model for the current request
     */
    public function loadRESTParams()
    {
        $formClassName = $this->restParamsClassName ?: $this->guessClassPrefix().'Params';
        $form = new $formClassName($this->getDefaultRESTParamsScenario());
        $form->setAttributes($this->getRawActionParams()->toArray());
        return $form;
    }

    /**
     * Returns the scenario for the action params model
     *
     * Defaults to the current action id if it is set, otherwise empty.
     *
     * @return string  scenario in which [_restParams] will be instantiated
     */
    public function getDefaultRESTParamsScenario()
    {
        return isset($this->action->id) ? $this->action->id : '';
    }

    /**
     * Returns the raw action params data parsed by the [_restAdaptor]
     *
     * @return array  [_restParams] attributes
     */
    public function getRawActionParams()
    {
        return $this->restAdaptor->getRawActionParams();
    }

    /**
     * Returns the unique id for the current action
     *
     * @return string  unique identifier for the current module/controller/action
     */
    public function getUniqueActionId()
    {
        return md5(implode('.', array(
            $this->getUniqueId(),
            isset($this->action->id) ? $this->action->id : null,
        )));
    }

    /**
     * Returns action configurations
     *
     * The default set of actions allows allows for the standard CRUD actions following
     * the Ruby on Rails convention that POST and GET versions of the same action be
     * handled differently. (E.g. "new" renders the create form, while "create" actually
     * creates the record.)
     *
     * @return array  action configurations indexed by action id
     */
    public function actions()
    {
        return array(
            'view'    =>array('class' =>'yii-rest.actions.RESTActionLoad'),
            'list'    =>array('class' =>'yii-rest.actions.RESTActionLoad'),
            'new'     =>array('class' =>'yii-rest.actions.RESTActionValidate'),
            'edit'    =>array('class' =>'yii-rest.actions.RESTActionValidate'),
            'update'  =>array('class' =>'yii-rest.actions.RESTActionSave', 'view' =>'//shared/save'),
            'create'  =>array('class' =>'yii-rest.actions.RESTActionSave', 'view' =>'//shared/save'),
            // 'delete'  =>array('class' =>'yii-rest.actions.RESTActionDelete', 'view' =>'//shared/delete'),
        );
    }

    /**
     * Returns filter configurations
     *
     * There are three default filters:
     * - Verb filters:  These restrict access to backend methods ('update', 'delete', 'create') based on HTTP method (400)
     * - Param filter:  This validates the action parameters model and throws an exception if invalid (400)
     * - Access check:  The standard access control filter (403)
     *
     * @return array  filter configurations
     */
    public function filters()
    {
        return CMap::mergeArray(array(
            array('RESTVerbsFilter', 'actions' =>array('create'), 'verbs' =>array('POST')),
            array('RESTVerbsFilter', 'actions' =>array('update'), 'verbs' =>array('POST', 'PATCH', 'PUT')),
            array('RESTVerbsFilter', 'actions' =>array('delete'), 'verbs' =>array('POST', 'DELETE')),
            array('RESTParamsFilter'),
            'accessControl',
        ), parent::filters());
    }

    protected function guessClassPrefix()
    {
        $className = get_class($this);
        return ucfirst(substr($className, 0, strlen($className) - 10));
    }
}