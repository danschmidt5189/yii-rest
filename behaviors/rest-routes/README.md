# Model REST Routing Behavior

This behavior encapsulates information about routes that can be used
to perform actions on a model. The purpose is to allow models to describe
the routes (ie links) that can be used to act on them.

Routes are specified in the following way:

```
// In your behavior config
'actions' =>array(
    'routeName' =>array(
        'controller/action',
        'param1' =>'fixedValue',
        'param2' =>'{{ attributePlaceholder }}'
    ),
),
```

For example, suppose you have a customer model which can be owned by other models, and
that your `list` action supports scoping based on the owner. You could write
that as follows:

```
'list' =>array(
    'customers/list',
    'ownerId' =>'{{ owner_id }}',
),
```

Then in your views (or anywhere), you can write:

```
$model = new Customer();
$model->ownedBy(1);
$model = $model->find();
print_array(Customer::model()->routes->list);
print_array($model->routes->list);

// Returns something like
array( 'customers/list' ) // The un-scoped model route
array( 'customers/list', 'ownerId' =>1 ) // The scoped model route
```