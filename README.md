<h1 align="center">Laravel Soulbscription</h1>

<p align="center"><a href="https://packagist.org/packages/lucasdotvin/laravel-soulbscription"><img alt="Latest Version on Packagist" src="https://img.shields.io/packagist/v/lucasdotvin/laravel-soulbscription.svg?style=flat-square"></a>
<a href="https://github.com/lucasdotvin/laravel-soulbscription/actions/workflows/run-tests.yml"><img src="https://github.com/lucasdotvin/laravel-soulbscription/actions/workflows/run-tests.yml/badge.svg?branch=main" alt="run-tests"></a>
<a href="https://codecov.io/gh/lucasdotvin/laravel-soulbscription"><img src="https://codecov.io/gh/lucasdotvin/laravel-soulbscription/branch/develop/graph/badge.svg?token=9NUYY1E28D"/></a>
<a href="https://github.com/lucasdotvin/laravel-soulbscription/actions/workflows/php-cs-fixer.yml"><img src="https://github.com/lucasdotvin/laravel-soulbscription/actions/workflows/php-cs-fixer.yml/badge.svg?branch=main" alt="Check &amp; fix styling"></a>
<a href="https://packagist.org/packages/lucasdotvin/laravel-soulbscription"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/lucasdotvin/laravel-soulbscription.svg?style=flat-square"></a></p>

## About

A straightforward interface to handle subscriptions and features consumption.

## Installation

You can install the package via composer:

```bash
composer require lucasdotvin/laravel-soulbscription
```

The package migrations are loaded automatically, but you can still publish them with this command:

```bash
php artisan vendor:publish --tag="soulbscription-migrations"
php artisan migrate
```

## Upgrades

If you already use this package and need to move to a newer version, don't forget to publish the upgrade migrations:

```bash
php artisan vendor:publish --tag="soulbscription-migrations-upgrades-1.x-2.x"
php artisan migrate
```

> Check out the available upgrade migrations by looking at the [upgrades folder](https://github.com/lucasdotvin/laravel-soulbscription/tree/develop/database/migrations/upgrades).

## Usage

To start using it, you just have to add the given trait to your `User` model (or any entity you want to have subscriptions):

```php
<?php

namespace App\Models;

use LucasDotVin\Soulbscription\Models\Concerns\HasSubscriptions;

class User
{
    use HasSubscriptions;
}
```

And that's it!

### Setting Features Up

First things first, you have to define the features you'll offer. In the example below, we are creating two features: one to handle how much minutes each user can spend with deploys and if they can use subdomains.

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use LucasDotVin\Soulbscription\Models\Feature;

class FeatureSeeder extends Seeder
{
    public function run()
    {
        $deployMinutes = Feature::create([
            'consumable'       => true,
            'name'             => 'deploy-minutes',
            'periodicity_type' => PeriodicityType::Day,
            'periodicity'      => 1,
        ]);

        $customDomain = Feature::create([
            'consumable' => false,
            'name'       => 'custom-domain',
        ]);
    }
}
```

By saying the `deploy-minutes` is a consumable feature, we are telling the users can use it a limited number of times (or until a given amount). On the other hand, by passing `PeriodicityType::Day` and 1 as its `periodicity_type` and `periodicity` respectively, we said that it should be renewed everyday. So a user could spend his minutes today and have it back tomorrow, for instance.

> It is important to keep in mind that both plans and consumable features have its periodicity, so your users can, for instance, have a monthly plan with weekly features.

The other feature we defined was `$customDomain`, which was a not consumable feature. By being not consumable, this feature implies only that the users with access to it can perform a given action (in this case, use a custom domain).

#### Postpaid Features

You can set a feature so it can be used over its charges. To do so, you just have to set the `postpaid` attribute to `true`:

```php
$cpuUsage = Feature::create([
    'consumable' => true,
    'postpaid'   => true,
    'name'       => 'cpu-usage',
]);
```

This way, the user will be able to use the feature until the end of the period, even if he doesn't have enough charges to use it (and you can charge him later, for instance).

#### Quota Features

When creating, for instance, a file storage system, you'll have to increase and decrease feature consumption as your users upload and delete files. To achieve this easily, you can use quota features. These features have an unique, unexpirable consumption, so they can reflect a constant value (as used system storage in this example).

```php
class FeatureSeeder extends Seeder
{
    public function run()
    {
        $storage = Feature::create([
            'consumable' => true,
            'quota'      => true,
            'name'       => 'storage',
        ]);
    }
}

...

class PhotoController extends Controller
{
    public function store(Request $request)
    {
        $userFolder = auth()->id() . '-files';

        $request->file->store($userFolder);

        $usedSpace = collect(Storage::allFiles($userFolder))
            ->map(fn (string $subFile) => Storage::size($subFile))
            ->sum();

        auth()->user()->setConsumedQuota('storage', $usedSpace);

        return redirect()->route('files.index');
    }
}
```

In the example above, we set `storage` as a quota feature inside the seeder. Then, on the controller, our code store an uploaded file on a folder, calculate this folder size by retrieving all of its subfiles, and, finally, set the consumed `storage` quota as the directory total size.

### Creating Plans

Now you need to define the plans available to subscription in your app:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use LucasDotVin\Soulbscription\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run()
    {
        $silver = Plan::create([
            'name'             => 'silver',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
        ]);

        $gold = Plan::create([
            'name'             => 'gold',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
        ]);
    }
}
```

Everything here is quite simple, but it is worth to emphasize: by receiving the periodicity options above, the two plans are defined as monthly.

#### Plans Without Periodicity ("Free Plans" or "Permanent Plans")

You can define plans without periodicity, so your users can subscribe to them permanently (or until they cancel their subscriptions). To do so, just pass a `null` value to the `periodicity_type` and `periodicity` attributes:

```php
$free = Plan::create([
    'name'             => 'free',
    'periodicity_type' => null,
    'periodicity'      => null,
]);
```

#### Grace Days

You can define a number of grace days to each plan, so your users will not loose access to their features immediately on expiration:

```php
$gold = Plan::create([
    'name'             => 'gold',
    'periodicity_type' => PeriodicityType::Month,
    'periodicity'      => 1,
    'grace_days'       => 7,
]);
```

With the configuration above, the subscribers of the "gold" plan will have seven days between the plan expiration and their access being suspended.

### Associating Plans with Features

As each feature can belong to multiple plans (and they can have multiple features), you have to associate them:

```php
use LucasDotVin\Soulbscription\Models\Feature;

// ...

$deployMinutes = Feature::whereName('deploy-minutes')->first();
$subdomains    = Feature::whereName('subdomains')->first();

$silver->features()->attach($deployMinutes, ['charges' => 15]);

$gold->features()->attach($deployMinutes, ['charges' => 25]);
$gold->features()->attach($subdomains);
```

It is necessary to pass a value to `charges` when associating a consumable feature with a plan.

In the example above, we are giving 15 minutes of deploy time to silver users and 25 to gold users. We are also allowing gold users to use subdomains.

### Subscribing

Now that you have a set of plans with their own features, it is time to subscribe users to them. Registering subscriptions is quite simple:

```php
<?php

namespace App\Listeners;

use App\Events\PaymentApproved;

class SubscribeUser
{
    public function handle(PaymentApproved $event)
    {
        $subscriber = $event->user;
        $plan       = $event->plan;

        $subscriber->subscribeTo($plan);
    }
}
```

In the example above, we are simulating an application that subscribes its users when their payments are approved. It is easy to see that the method `subscribeTo` requires only one argument: the plan the user is subscribing to. There are other options you can pass to it to handle particular cases that we're gonna cover below.

> By default, the `subscribeTo` method calculates the expiration considering the plan periodicity, so you don't have to worry about it.

#### Defining Expiration and Start Date

You can override the subscription expiration by passing the `$expiration` argument to the method call. Below, we are setting the subscription of a given user to expire only in the next year.

```php
$subscriber->subscribeTo($plan, expiration: today()->addYear());
```

It is possible also to define when a subscription will effectively start (the default behavior is to start it immediately):

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentStoreFormRequest;
use App\Models\Course;
use App\Models\User;
use LucasDotVin\Soulbscription\Models\Plan;

class StudentController extends Controller
{
    public function store(StudentStoreFormRequest $request, Course $course)
    {
        $student = User::make($request->validated());
        $student->course()->associate($course);
        $student->save();

        $plan = Plan::find($request->input('plan_id'));
        $student->subscribeTo($plan, startDate: $course->starts_at);

        return redirect()->route('admin.students.index');
    }
}
```

Above, we are simulating an application for a school. It has to subscribe students at their registration, but also ensure their subscription will make effect only when the course starts.

### Switching Plans

Users change their mind all the time and you have to deal with it. If you need to change the current plan of a user, simply call the method `switchTo`:

```php
$student->switchTo($newPlan);
```

If you don't pass any arguments, the method will suppress the current subscription and start a new one immediately.

> This call will fire a `SubscriptionStarted(Subscription $subscription)` event.

### Fetching Current Balance

If you need remaining charges of a user, simply call the method `balance`. Imagine a scenario where a student has consumable feature named `notes-download`. To get remaining downloads limit:
```php
$student->balance('notes-download');
```

> This is just an alias of `getRemainingCharges` added to enrich the developer experience. 

#### Scheduling a Switch

If you want to keep your user with the current plan until its expiration, pass the `$immediately` parameter as `false`:

```php
$primeMonthly = Plan::whereName('prime-monthly')->first();
$user->subscribeTo($primeMonthly);

...

$primeYearly = Plan::whereName('prime-yearly')->first();
$user->switchTo($primeYearly, immediately: false);
```

In the example above, the user will keep its monthly subscription until its expiration and then start on the yearly plan. This is pretty useful when you don't want to deal with partial refunds, as you can bill your user only when the current paid plan expires.

Under the hood, this call will create a subscription with a start date equal to the current expiration, so it won't affect your application until there.

> This call will fire a `SubscriptionScheduled(Subscription $subscription)` event.

#### Renewing

To renew a subscription, simply call the `renew()` method:

```php
$subscriber->subscription->renew();
```

> This method will fire a `SubscriptionRenewed(Subscription $subscription)` event.

It will calculate a new expiration based on the current date.

#### Expired Subscriptions

In order to retrieve an expired subscription, you can use the `lastSubscription` method:

```php
$subscriber->lastSubscription();
```

This method will return the last subscription of the user, regardless of its status, so you can, for instance, get an expired subscription to renew it.:

```php
$subscriber->lastSubscription()->renew();
```

#### Canceling

> There is a thing to keep in mind when canceling a subscription: it won't revoke the access immediately. To avoid making you need to handle refunds of any kind, we keep the subscription active and just mark it as canceled, so you just have to not renew it in the future. If you need to suppress a subscription immediately, give a look on the method `suppress()`.

To cancel a subscription, use the method `cancel()`:

```php
$subscriber->subscription->cancel();
```

This method will mark the subscription as canceled by filling the column `canceled_at` with the current timestamp.

> This method will fire a `SubscriptionCanceled(Subscription $subscription)` event.

#### Suppressing

To suppress a subscription (and immediately revoke it), use the method `suppress()`:

```php
$subscriber->subscription->suppress();
```

This method will mark the subscription as suppressed by filling the column `suppressed_at` with the current timestamp.

> This method will fire a `SubscriptionSuppressed(Subscription $subscription)` event.

#### Starting

To start a subscription, use the method `start()`:

```php
$subscriber->subscription->start(); // To start it immediately
$subscriber->subscription->start($startDate); // To determine when to start
```

> This method will fire a `SubscriptionStarted(Subscription $subscription)` event when no argument is passed, and fire a `SubscriptionStarted(Subscription $subscription)` event when the provided start date is future.

This method will mark the subscription as started (or scheduled to start) by filling the column `started_at`.

### Feature Consumption

To register a consumption of a given feature, you just have to call the `consume` method and pass the feature name and the consumption amount (you don't need to provide it for not consumable features):

```php
$subscriber->consume('deploy-minutes', 4.5);
```

The method will check if the feature is available and throws exceptions if they are not: `OutOfBoundsException` if the feature is not available to the plan, and `OverflowException` if it is available, but the charges are not enough to cover the consumption.

> This call will fire a `FeatureConsumed($subscriber, Feature $feature, FeatureConsumption $featureConsumption)` event.

#### Check Availability

To check if a feature is available to consumption, you can use one of the methods below:

```php
$subscriber->canConsume('deploy-minutes', 10);
```

To check if a user can consume a certain amount of a given feature (it checks if the user has access to the feature and if he has enough remaining charges).

```php
$subscriber->cantConsume('deploy-minutes', 10);
```

It calls the `canConsume()` method under the hood and reverse the return.

```php
$subscriber->hasFeature('deploy-minutes');
```

To simply checks if the user has access to a given feature (without looking for its charges).

```php
$subscriber->missingFeature('deploy-minutes');
```

Similarly to `cantConsume`, it returns the reverse of `hasFeature`.

### Feature Tickets

Tickets are a simple way to allow your subscribers to acquire charges for a feature. When a user receives a ticket, he is allowed to consume its charges, just like he would do in a normal subscription. Tickets can be used to extend regular subscriptions-based systems (so you can, for instance, sell more charges of a given feature) or even to **build a fully pre-paid service**, where your users pay only for what they want to use.

#### Enabling Tickets

In order to use this feature, you have to enable tickets in your configuration files. First, publish the package configs:

```bash
php artisan vendor:publish --tag="soulbscription-config"
```

Finally, open the `soulbscription.php` file and set the `feature_tickets` flag to `true`. That's it, you now can use tickets!

#### Creating Tickets

To create a ticket, you can use the method `giveTicketFor`. This method expects the feature name, the expiration and optionally a number of charges (you can ignore it when creating tickets for not consumable features):

```php
$subscriber->giveTicketFor('deploy-minutes', today()->addMonth(), 10);
```

> This method will fire a `FeatureTicketCreated($subscriber, Feature $feature, FeatureTicket $featureTicket)` event.

In the example above, the user will receive ten more minutes to execute deploys until the next month.

#### Not Consumable Features

You can create tickets for not consumable features, so your subscribers will receive access to them just for a certain period:

```php
class UserFeatureTrialController extends Controller
{
    public function store(FeatureTrialRequest $request, User $user)
    {
        $featureName = $request->input('feature_name');
        $expiration = today()->addDays($request->input('trial_days'));
        $user->giveTicketFor($featureName, $expiration);

        return redirect()->route('admin.users.show', $user);
    }
}
```

#### Non-Expirable Tickets

You can create tickets that never expire, so your subscribers will receive access to them forever:

```php
$subscriber->giveTicketFor('deploy-minutes', null, 10);
```

> Don't forget to remove these tickets when your user cancels his subscription. Otherwise, they will be able to consume the charges forever.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Lucas Vinicius](https://github.com/lucasdotvin)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
