# Yii REST

## REST Controller

Each REST controller handles a specific class of resources. It does not instantiate the resource directly. That function is
delegated to the controller's parameter model. (See below.)

The included RESTController base class handles:

- Filtering invalid requests by HTTP method
- Loading the appropriate action parameter model
- Filtering out invalid action parameters

When inheriting from RESTController, you must implement:

- Your controller's parameter model, which is a FormModel used to validate and later bind your action parameters
- getRawActionParams(), which maps request parameters to the attributes of your action params model. This maps
  public (web) request properties to your internal model interface. If your model, the request, and your actions
  share the same parameter names, you won't need to override this.

```php
<?php
/**
 * Example: A RESTful customers controller.
 */
class CustomersController extends RESTController
{
    /**
     * @return array  raw action parameters
     */
    public function getRawActionParams()
    {
        return array(
            'id' =>$request->getQuery('id'),
            'type' =>$request->getQuery('type'),
            'data' =>$request->getParam('Customer'),
        );
    }

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

    // Model property is inherited from RESTParams
    // $model

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
            // Data is an attributes array
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