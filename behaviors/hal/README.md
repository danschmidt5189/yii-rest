# HAL

HAL, or Hypertext Application Language, is a standardized way of representing resource data
in a REST context using JSON. A description is available at [[http://stateless.co/hal_specification.html]].

The four main HAL components are all supported:

1. attributes: By default, these are the safe attributes of your model.
2. _meta: By default, this is isNewRecord, the className, and the primaryKey.
3. _links: Empty by default. Ultimately the goal is to map this to action routes using the RESTRoutesBehavior.
4. _embedded: Empty by default. Ultimately the goal is to map this to AR relations.

```
// Echo a HAL JSON representation of the model.
echo $model->toHal();
// Something like...
{
    id: 1,
    name: 'Dan',
    _embedded: [],
    _links: [],
    _meta: {
        isNewRecord: true,
        primaryKey: 'id',
        className: 'Guy'
    }
}
```