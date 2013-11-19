# Yii REST

The Yii REST extension adds classes and filters that help you write RESTful controllers. Key features:

1. A facade layer for translating client requests to a format your controller understands
2. A parameter model for validating and configuring your action parameters in an OOP way
3. A standard set of Actions for all your CRUD needs
4. A works-out-of-the-box RESTController with verb and parameter filtering

## REST Controller

A RESTController manages a class of resources in an OOP fashion. It introduces two new properties:

- `$_facade`: A mapper that translates client requests into a standard internal format. This is analogous
  to `CController::getActionParams()`.
- `$_actionParams`: A form model representing the action parameters. This can be validated by your filters,
  and it is also used to directly bind your controller action parameters.

```php
<?php
/**
 * Example: A RESTful customers controller.
 *
 * The default implementation will load a facade component called `CustomersFacade` and a parameter
 * model called `CustomersParams`. The rest is done for you. The parent implementation adds standard
 * REST verb filters and validates the action parametermodel.
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

## REST Facade: Fancy `getActionParams()`

The facade extracts raw action parameters from the client request. It is analogous to `CController::getActionParams()`.

The facade layer manages two things:

- Where to look for data in the client request. (E.g. GET, POST, ...)
- How to map a client key to an internal parameter attribute (E.g. $_GET["Customer"] =>$params->filters)

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

## RESTParams: Fancy `loadModel()`

RESTParams is a model representing the parameters passed as arguments to your controller actions. A standard way to use
this would be to expose a `$model` property which is constructed with some initial, unchangeable settings (e.g. scopes)
along with modifications due to the current request. (E.g., "If 'id' is set, load that specific customer after applying all
scopes and filters.")

The scenario of a RESTParams model should correspond to an Action ID.

```php
<?php
/**
 * Represents action parameters for the CustomersController.
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