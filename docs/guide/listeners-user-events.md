---
outline: deep
---

# User Event Listeners

Stickle makes it easy to listen for and respond to User initiated events.

To recap, User events can be triggered via:

-   the Javascript SDK using the stickle.trackEvent() method; or
-   on the server by dispatching the `Dclaysmith\LaravelCascade\Events\Track` event.

## Creating Listeners

To respond to these events, you can create a Laravel class with a name constructed using the name of the event (converted to camel case) and suffixed with "Listener". This class must exist in the listeners directory specifiec in `config('stickle.paths.listeners')`.

For example, a class named named `App\Listeners\IDidAThingListener` will be executed when any of the following events are dispatched:

-   i:did\:a:thing
-   i_did_a_thing
-   IDidAThing
