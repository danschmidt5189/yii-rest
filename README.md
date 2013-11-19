# Yii REST

## REST Controller

Each REST controller handles a specific class of resources.

There are two key additions to REST controllers that differentiate them from normal controllers:

1. RESTEndpoint, which extracts raw action parameters from the request. This maps the controller's public interface
   to its private interface and describes what data the controller understands from the client.
2. RESTParams, which processes the raw action parameters. This is responsible for validating and filtering the
   raw parameters so they can be passed to an action.

Simply put, the RESTEndpoint takes a raw request and maps it into something that can be set as the attributes
of the RESTParams model. The RESTParams model validates the mapped data and exposes it to the controller actions.

Think of RESTEndpoint as an OOP version of getActionParams(), and RESTParams as an OOP version of loadModel().

```php
<?php
/**
 * Example: A RESTful customers controller.
 */
class CustomersController extends RESTController
{
    /**
     * @var string  name of the endpoint (public interface:private interface mapper) class for this controller
     */
    public $endpointClassName = 'CustomersEndpoint';

    /**
     * @var string  name of the action parameter class for this controller
     */
    public $paramsClassName = 'CustomersParams';

    /**
     * Returns action configurations
     *
     * Filtering of invalid methods is handled by [RESTController::filters()].
     *
     * @return array  RESTAction configurations indexed by action id
     */
    public function actions()
    {
        return array(
            // Renders a form for creating a Customer
            'new' =>array('class' =>'yii-rest.actions.RESTActionValidate'),
            // Creates a new Customer
            'create' =>array('class' =>'yii-rest.actions.RESTActionSave'),
            // Renders a searchable list of Customers
            'list' =>array('class' =>'yii-rest.actions.RESTActionLoad'),
            // Renders details about a specific customer
            'view' =>array('class' =>'yii-rest.actions.RESTAction'),
            // Renders a form for editing a specific Customer
            'edit' =>array('class' =>'yii-rest.actions.RESTActionValidate'),
            // Updates a specific Customer
            'update' =>array('class' =>'yii-rest.actions.RESTActionSave'),
            // Deletes a specific customer
            'delete' =>array('class' =>'yii-rest.actions.RESTActionDelete'),
        );
    }
}
?>
```

## Action Parameters

The parameter model replaces Yii's standard loadModel() method, encapsulating that logic into a form class that
is used to validate request parameters as well as bind parameters to your controller actions.

A parameter model can be any CFormModel, but to work with the included RESTAction classes you should inherit
from RESTParams, which includes some helpful methods and validation rules for loading and handling the model.
When inheriting from this class, you must implement the loadModel() method to load the model property.

```php
<?php
class CustomersParams extends RESTParams
{
    /**
     * @var integer  customer id. If set, [loadModel()] will attempt to load this customer after
     *                            applying all other filters and scopes.
     */
    public $id;

    /**
     * @var integer  customer type. If set, scope is restricted to customers of this type.
     */
    public $type;

    /**
     * @var array  customer attributes made available to the action
     */
    public $data;

    /**
     * Inherited from RESTParams
     */
    // public $model;

    /**
     * @return Customer  the customer model for this request
     */
    public function loadModel()
    {
        $user = Yii::app()->getUser();

        $model = new Customer();
        // Apply scopes first
        $model->scopeByOwner($user->id)->scopeByStatus(Customer::STATUS_ACTIVE);
        if ($this->type) {
            $model->scopeByType($this->type);
        }
        // Scenario-specific rules
        if ('list' === $this->scenario) {
            $model->setScenario('search');
            $model->unsetAttributes();
        } else if ('create' === $this->scenario) {
            $model->owner_id = $user->id;
        }
        // Attempt to retrieve a specific customer
        if ($this->id) {
            $model = $model->findByPk($this->id);
        }
        return $model;
    }

    /**
     * Returns validation rules
     *
     * Scenario names are action IDs.
     *
     * @return array  validation rule configurations
     */
    public function rules()
    {
        return CMap::mergeArray(array(
            // ID is only valid on actions dealing with a specific customer
            array(
                'id',
                'required',
                'on' =>array('view', 'update', 'delete'),
            ),
            array(
                'type',
                'in',
                'range' =>array(Customer::ACTIVE, Customer::DISABLED),
                'on' =>array('list'),
            ),
            // Data is an array of attributes
            array(
                'data',
                'type',
                'type' =>'array',
            ),
        ), parent::rules());
    }
}
?>
```

## Endpoints

Endpoints map HTTP request parameters to the parameters of your controller's action parameter model. The endpoint
provides the raw action parameters that are loaded into your controller's action parameter model.

Endpoints accomplish two things:

- They map external request keys to internal ones used by your parameter model. (E.g. $_POST["Customer"] to $model["data"])
- They specify the expected source for data. (E.g. GET, POST, etc.)

```php
<?php
class CustomersEndpoint extends RESTEndpoint
{
    public $interface = array(
        array('QUERY', 'id'),
        array('QUERY', 'type'),
        // The 'Customer' request parameter is mapped to the 'data' attribute of
        // the CustomersParams model.
        array('ANY', 'Customer' =>'data'),
    );
}
?>
```

## Filters

Two filters are bundled with this extension:

- RESTVerbsFilter, for filtering out invalid requests based on the HTTP method
- RESTParamsFilter, which filters out invalid requests using a form to validate action parameters

These are pre-configured in the RESTController as follows:

```php
<?php
public function filters()
{
    return array(
        array('RESTVerbsFilter', 'actions' =>array('update'), 'verbs' =>array('PUT', 'PATCH')),
        array('RESTVerbsFilter', 'actions' =>array('create'), 'verbs' =>array('POST')),
        array('RESTVerbsFilter', 'actions' =>array('delete'), 'verbs' =>array('DELETE')),
        array('RESTParamsFilter'),
    );
}
?>
```