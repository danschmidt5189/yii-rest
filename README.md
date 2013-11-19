# Yii REST

The Yii REST extension adds classes and filters that help you write RESTful controllers. Key features:

1. A facade layer for translating client requests to a format your controller understands
2. A parameter model for validating and configuring your action parameters in an OOP way
3. A standard set of Actions for all your CRUD needs
4. A works-out-of-the-box RESTController with verb and parameter filtering

## REST Controller

#### A little bit Yii, a lot of bit Symfony2

The RESTController introduces two new properties:

- `$_actionParams`: A form model representing the parameters passed to your controller actions.
- `$_facade`: A class that maps client requests to a format understood by the `$_actionParams` model.

There are several benefits to including these new components:

- We can modify the client interface by changingt the facade layer without changing any other code.
- We can seemlessly switch in/out parameter models having the same property interface.
- We can validate the parameter model to easily return informative API error messages to the client.

```php
<?php
/**
 * RESTful customers controller
 *
 * The default implementation will load a facade component called `CustomersFacade` and a parameter
 * model called `CustomersParams`. The rest is done for you. The parent implementation adds standard
 * REST verb filters and validates the action parametermodel.
 *
 * @see CustomersFacade
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

## REST Facade

#### A fancy version of `getActionParams()`

The facade maps the client request to the attributes of the parameter model. It indicates three things:

- What public parameters the controller looks for.
- Where the controller looks for those parameters. (E.g. GET, POST, ...)
- What internal parameter the public parameter corresponds to

```php
<?php
/**
 * Represents the public : private adaptor for the CustomersController
 */
class CustomersEndpoint extends RESTEndpoint
{
    public $interface = array(
        // Here the public and private keys are identical
        array('QUERY', 'id'),
        array('QUERY', 'type'),
        // Here the client provides the 'Customer' parameter, which is mapped
        // to the 'data' property of the params model
        array('ANY', 'Customer' =>'data'),
    );
}
?>
```

## RESTParams

#### A fancy version of `loadModel()`

RESTParams validates raw request parameters parsed by the facade layer and makes them available to your actions. Scenarios
should correspond to an Action ID. When extending `RESTParams`, you are required to implement the `loadModel()` method,
however it is not required that you do so. (Any CFormModel will also work.)

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
        ), parent::rules());
    }
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