---
outline: deep
---

# Javascript SDK

Stickle can optionally inject a lightweight, Javascript tracking code into your Laravel app. You can specify this behavior in the `config('stickle.tracking.client.loadMiddleware')`.

By default, this code will register user-defined events and page views.

## Methods

### Identify

You can use this to send User attributes from the client. It is unlikely that you will utilize this method as you should have access to the informantion on the server.

```js
stickle.identify();
```

### Group

You can use this to send Group attributes from the client. It is unlikely that you will utilize this method as you should have access to the informantion on the server.

```js
stickle.group();
```

### Page

```js
stickle.page();
```

### Track

```js
stickle.track();
```

## Securing Your Tracking Code

bcrypt()
