---
outline: deep
---

[//]: <> (https://stackoverflow.com/a/17250429)

# Model Attribute Listeners

Stickle can listen for changes to specific attributes of your Models.

## Standard Attributes

Model attributes that exist as table columns are tracked in real-time. When a new value is saved for the attribute, an ObjectAttributeChanged is dispatched immediately.

## Custom Attributes

You can define custom or calculated attributes in Laravel by adding a function with the following naming convention `get` + AttributeName + `Attribute()`. The values for these attributes are calculated at runtime and can't be tracked like standard attributes.

To track these attributes, Stickle will periodically fetch the values for these attributes and, if changed, dispatch an ObjectAttributeChanged event.

## Listening for Changes

You can respond to changes in Model Attributes by creating a listener with the following naming convention:
**ModelName** + **AttributeMame** + `Listener`. The `handle()` method should accept a `StickleApp\Core\Events\ObjectAttributeChanged` event as its only parameter.

For instance, if you want to respond to changes in the Users `order_count` attribute:

1. Insure that it is included in the `$observedAttributes` array of the User class;
2. Create a listener named `App\Listeners\UserOrderCountListener`;
3. Add a handler method:

```php
public function handle(ObjectAttributeChanged $event): void {}
```
