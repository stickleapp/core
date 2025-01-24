---
outline: deep
---

# Request Middleware

Stickle can optionally inject middleware into your application that will log each authenticated request. This can be useful for tracking user behavior on traditional Laravel apps as well as for tracking API requests.

This behavior can be enabled in the `config('sticke.tracking.server.loadMiddleware')` setting.
This can create unwanted noise for single-page apps.

## With Livewire

## With Inertia
