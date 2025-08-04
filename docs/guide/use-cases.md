---
outline: deep
---

# Use Cases

Stickle is extremely powerful yet easy to get started with. Stickle helps you answer questions you have about your customers and put that information to work for you.

It can help answer:

## Who are my customers?

Stickle has you covered. Follow the [installation instructions](/guide/installation.md) and navigate to `/stickle`. You can see:

-   A list of your 'customers' (Laravel models you have assigned the `StickleEntity` trait).
-   Related `StickleEntity` models (ex. `Company Users`, `Admin Users`, `Parent Company`); and
-   'Customer' entity details.

## How are people using my app?

Stickle is already processing your user data and making it available in snazzy charts and data tables. It reports:

-   Sessions; <Badge type="warning" text="Coming Soon!" />
-   Page Views;
-   Authorization events; and
-   User-defined events.

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

## Engage with your customers in real-time

Stickle is designed to be the central hub for engaging with your customers. Using Stickle and built-in Laravel features, you can quickly add real-time functionality to your application:

-   Trigger emails when customers haven't logged in recently;
-   Add new users to your CRM automatically;
-   Display notifications when users visit specific pages.

You can easily add Intercom-style interctions to your Laravel application using Stickle.
