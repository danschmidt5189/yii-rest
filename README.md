# Yii REST

The Yii REST extension adds classes and filters that help you write RESTful controllers.

Key components:

- RESTAdaptor: An adaptor layer translating raw client request data to a format understood by your
  controller actions. An OOP version of `CController::getActionParams()`.
- RESTParams: A form model responsible for validating and loaded your action parameters based on the
  raw data parsed by the adaptor. An OOP version of `loadModel()`.
- RESTController: A base controller that ties together the two components above along with sensible
  default filters, e.g. verbs and validation filtering.
- RESTAction(s): A set of lightweight actions that handle all of your CRUD needs.

The ultimate goal is to create controllers and actions that are extremely thin, with swappable components
for both their public interface and parameter generation, and that can be described programatically for later
documentation via the HTTP OPTIONS method.

In addition, the controllers should be format-agnostic. Actions should only expose raw data via `RESTAction::getResult()`,
which the controller can translate (through delegation) to a response that converts the raw data into a type
allowed by the user. (E.g. JSON, HTML, XML, ...) However this is not currently implemented, as it is a feature
much more easily handled using the upcoming Yii2.

## RESTController

The RESTController introduces two new properties:

- `$_restParams`: A form model representing the parameters passed to your controller actions. This is analogous
  to the `loadModel()` method in the standard Gii crud controllers, but in an OOP fashion.
- `$_restAdaptor`: A class that maps client request data to `$_restParams` attributes. This is analogous to
  `CController::getActionParams()` in that it returns the raw request data bound to your action parameters.

There are several benefits to including these new components:

- To change how client requests are mapped to the back-end, we need only change the adaptor layer
- We can seemlessly swap between parameter models having the same property interface. (E.g. one model
  for admins and another for standard users.)
- We can easily return informative API error messages by validating the parameter model
- We keep our controllers (and actions) super thin by delegating complex loading/initialization logic
  to dedicated classes.

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

The adaptor maps the client request to the attributes of the params model.

Its configuration indicates three things:

- What public params the controller looks for
- What internal param the public param maps to
- Where the controller looks for the public param. (E.g. GET, POST, ...)

`RESTAdaptor::getRawActionParams()` parses the current request and returns the client parameters mapped
to the attributes you specify in `RESTAdaptor::$interface`.

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
     * Loads the Customer model
     *
     * The model's scope is always restricted to return only active customers owned by the current user.
     * If the `type` attribute is set, only customers of that type will be returned. If the `id`
     * attribute is set, this will attempt to load that Customer. (After applying all filters.)
     *
     * You should not call this method directly. It is set a a filter on the [model] property in
     * [RESTParams::rules()].
     *
     * @return Customer  the customer model for this request
     */
    protected function loadModel()
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
        return CMap::mergeArray(
            array(
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
            // Merge parent rules so that [model] is loaded, required, and declared unsafe.
            parent::rules()
        );
    }
}
?>
```

## Filters

Two filters are bundled with this extension:

- RESTVerbsFilter, which filters requests which use the wrong HTTP method
- RESTParamsFilter, which filters requests where `$_restParams` is invalid

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