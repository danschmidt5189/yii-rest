# Yii REST

The Yii REST extension adds classes and filters that help you write RESTful controllers. Key features:

1. A Adaptor layer for translating client requests to a format your controller understands
2. A parameter model for validating and configuring your action parameters in an OOP way
3. A standard set of Actions for all your CRUD needs
4. A works-out-of-the-box RESTController with verb and parameter filtering

## RESTController

#### A few ideas borrowed from Symfony

The RESTController introduces two new properties:

- `$_restParams`: A form model representing the parameters passed to your controller actions.
- `$_restAdaptor`: A class that maps client request data to `$_restParams` attributes.

There are several benefits to including these new components:

- To change how client requests are mapped to the back-end, we need only change the adaptor layer
- We can seemlessly swap between parameter models having the same property interface. (E.g. one model
  for Admins and another for standard users.)
- We can easily return informative API messages by validating the parameter model

### Example: CustomersController.php

```php
<?php
/**
 * RESTful customers controller
 *
 * The default implementation will load a Adaptor component called `CustomersAdaptor` and a params
 * model called `CustomersParams`. The rest is done for you. The parent implementation adds standard
 * REST verb filters and validates the params model.
 *
 * @see CustomersAdaptor
 * @see CustomersParams
 */
class CustomersController extends RESTController
{
    /**
     * Returns action configurations
     *
     * Filtering of invalid methods is handled by [RESTController::filters()].
     *
     * @return array  RESTAction configurations indexed by action id
     * @see yii-rest.actions for example action classes
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

## RESTAdaptor

#### A fancy version of `getActionParams()`

The adaptor maps the client request to the attributes of the params model.

Its configuration indicates three things:

- What public params the controller looks for
- What internal param the public param maps to
- Where the controller looks for the public param. (E.g. GET, POST, ...)

### Example: CustomersAdaptor.php

```php
<?php
/**
 * The client->backend adaptor for the CustomersController
 */
class CustomersAdaptor extends RESTAdaptor
{
    public $interface = array(
        // Here the public and private keys are identical
        array(RESTSource::GET, 'id'),
        array(RESTSource::GET, 'type'),
        // Here the client provides the 'Customer' parameter, which is mapped
        // to the 'data' property of the params model
        array(RESTSource::ANY, 'Customer' =>'data'),
    );
}
?>
```

## RESTParams

#### A fancy version of `loadModel()`

RESTParams validates and processes raw request parameters extracted by the adaptor and makes them available to your actions.

`RESTParams::$scenario` should correspond to an action ID.

If implementing RESTParams, you must implement the `loadModel()` method. However, you do not have to implement RESTParams;
any CFormModel will also work, with its public properties used to bind action parameters.

### Example: CustomersParams.php

```php
<?php
/**
 * Represents action parameters for the CustomersController
 */
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
            array(
                'data',
                'type',
                'type' =>'array',
            ),
        ),
        // Merge parent rules so that the model is loaded, required,
        // and declared unsafe.
        parent::rules());
    }
}
?>
```

## Filters

Two filters are bundled with this extension:

- RESTVerbsFilter, for filtering out invalid requests based on the HTTP method
- RESTParamsFilter, which filters out invalid requests using a form to validate action parameters

(There is an empty base `yii-rest.components.RESTFilter` that you can override as you see fit.)

These are pre-configured in the RESTController as follows:

```php
<?php
class RESTController extends CController {
    ...
    public function filters()
    {
        return array(
            array('RESTVerbsFilter', 'actions' =>array('update'), 'verbs' =>array('PUT', 'PATCH')),
            array('RESTVerbsFilter', 'actions' =>array('create'), 'verbs' =>array('POST')),
            array('RESTVerbsFilter', 'actions' =>array('delete'), 'verbs' =>array('DELETE')),
            array('RESTParamsFilter'),
        );
    }
    ...
}
?>
```