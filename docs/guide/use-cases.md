---
outline: deep
---

# Use Cases

Stickle is extremely powerful&mdash;if you need it to be. But it very easy to get started.

## How are people using my app?

Stickle has you covered. Follow the [installation instructions](/guide/installation.md) and navigate to `/stickle`.

Stickle is already processing your user data and making it available in snazzy charts and data tables. It reports:

-   Sessions;
-   Page Views;
-   Authorization events; and
-   User-defined events.

Have a click around and then come back here!

## Let's Segment your customers!

Not all customers are alike. If you know Laravel Eloquent, then you can build powerful Segments. Here are some ideas:

-   Customers who joined this week.
-   Customers who have paid the most money.
-   Customers who have been active the longest.
-   Customers who have made the most purchases.
-   Customers renewing in the next 30- (or 60- or 90-) days.
-   ICP (Ideal Customer Profile)

We've already created a few of these for you to use as examples.

Once you have built a Segment, Stickle will start tracking your customer KPIs at the Segment-level (ex. what is the MRR of Customers renewing in the next 30 days).

## I need more customer data!

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
