---
outline: deep
---

# Javascript SDK

Stickle can optionally inject a lightweight, Javascript tracking code into your Laravel app. You can specify this behavior in the `config('stickle.tracking.client.loadMiddleware')`.

By default, this code will register users' page views but you can configure it to track custom events as well.

## Methods

These are the methods exposed by the Javascript tracking code.

### Page

The `page` method is called automatically when your users navigate through your application. You can also call in manually.

```js
stickle.page();
```

### Track

You can also define custom events and send them to the server.

```js
stickle.track(eventName, eventData);
```
