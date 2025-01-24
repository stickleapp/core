---
outline: deep
---

# Getting Started

OK. You have installed and are running StickleCore. What now?

## Learn About Your Customers

Go to `/stickle` and you will see a list of your customers. This tells you a few interesting things:

-   Who is using your application _right now_;
-   When did each user login for the first time;
-   When did they most recently log in.

::: tip
Stickle does not know _first login_ or _most recent login_ if those events occurred before you installed Stickle. However, you can backfill this data through the `/stickle/load-data` endpoint.
:::

Do you want to see what your Users are collectively doing in real-time? Navigate to `/stickle/events` and see these events streaming in.

Click on one of the Users and we can have a deeper look.

The User view will show you:

-   A history of the Users sessions over time;
-   Which pages they have visited.

1. First install it and configure it...

QuickStart SPA, Inertia, Livewire, Blade (sets reasonable defaults);

2. Install StickleUI and learn about your customers and how they use your application.

3. Set up internal notifications (alert me when a customer does X)
   ... can we come up with some when() method for only certain conditions?
   ... channels();

4. Set up customer notifications
   ... channels() email, in-app, sms

5. Create some admin dashboards.

-   Use Stickle Eloquent Methods (`stickle` and `orStickle`) in your app.
-   Add Stickle Charts to An Admin Dashboard (auth?)
    -   Copy and Paste:
        -   D3 (Vanilla)
        -   Rechart (React)
        -   Chart.js (Vue, Filament, Nova)
        -
-   Segment data tables

-   Send Emails (stickle-notifications-email)
-   Send Slack Notifications (stickle-notifications-slack)
-   Send In-App Notifications (stickle-notifications-widget)
-   Send Toast Notifications (stickle-notifications-toast)

https://neon.tech/guides/laravel-livewire-dynamic-charts
