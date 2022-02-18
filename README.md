<h1 align="center">Laravel Soulbscription</h1>

<p align="center"><a href="https://packagist.org/packages/lucasdotdev/laravel-soulbscription"><img alt="Latest Version on Packagist" src="https://img.shields.io/packagist/v/lucasdotdev/laravel-soulbscription.svg?style=flat-square"></a>
<a href="https://github.com/lucasdotdev/laravel-soulbscription/actions?query=workflow%3Arun-tests+branch%3Amain"><img alt="GitHub Tests Action Status" src="https://img.shields.io/github/workflow/status/lucasdotdev/laravel-soulbscription/run-tests?label=tests"></a>
<a href="https://github.com/lucasdotdev/laravel-soulbscription/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain"><img alt="GitHub Code Style Action Status" src="https://img.shields.io/github/workflow/status/lucasdotdev/laravel-soulbscription/Check%20&%20fix%20styling?label=code%20style"></a>
<a href="https://packagist.org/packages/lucasdotdev/laravel-soulbscription"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/lucasdotdev/laravel-soulbscription.svg?style=flat-square"></a></p>

## About

This package provides a straightforward interface to handle subscriptions and features consumption.

## Installation

You can install the package via composer:

```bash
composer require lucasdotdev/laravel-soulbscription
```

The package migrations are loaded automatically, but you can still publish them with this command:

```bash
php artisan vendor:publish --tag="laravel-soulbscription-migrations"
php artisan migrate
```

## Usage

To start using it, you just have to add the given trait to your `User` model (or any entity you want to have subscriptions):

```php
<?php

namespace App\Models;

use LucasDotDev\Soulbscription\Models\Concerns\HasSubscriptions;

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
use LucasDotDev\Soulbscription\Enums\PeriodicityType;
use LucasDotDev\Soulbscription\Models\Feature;

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

### Creating Plans

Now you need to define the plans available to subscription in your app:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LucasDotDev\Soulbscription\Enums\PeriodicityType;
use LucasDotDev\Soulbscription\Models\Plan;

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

### Associating Plans with Features

As each feature can belong to multiple plans (and they can have multiple features), you have to associate them:

```php
use LucasDotDev\Soulbscription\Models\Feature;

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
use LucasDotDev\Soulbscription\Models\Plan;

class StudentController extends Controller
{
    public function store(StudentStoreFormRequest $request, Course $course)
    {
        $student = User::make($request->validated());
        $student->course()->associate($course);
        $student->save();

        $plan = Plan::find($request->validated('plan_id'));
        $student->subscribeTo($plan, startDate: $course->starts_at);

        return redirect()->route('admin.students.index');
    }
}
```

Above, we are simulating an application for a school. It has to subscribe students at their registration, but also ensure their subscription will make effect only when the course starts.

### Switching Plans

Users change their mind all the time and you have to deal with it. If you need to change the current plan o a user, simply call the method `switchTo`:

```php
$student->switchTo($newPlan);
```

If you don't pass any arguments, the method will suspend the current subscription and start a new one immediately.

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

#### Renewing

To renew a subscription, simply call the `renew()` method:

```php
$subscriber->subscription->renew();
```

It will calculate a new expiration based on the current date.

#### Canceling

> There is a thing to keep in mind when canceling a subscription: it won't revoke the access immediately. To avoid making you need to handle refunds of any kind, we keep the subscription active and just mark it as canceled, so you just have to not renew it in the future. If you need to suspend a subscription immediately, give a look on the method `suspend()`.

To cancel a subscription, use the method `cancel()`:

```php
$subscriber->subscription->cancel();
```

This method will mark the subscription as canceled by filling the column `canceled_at` with the current timestamp.

#### Suspending

To suspend a subscription (and immediately revoke it), use the method `suspend()`:

```php
$subscriber->subscription->suspend();
```

This method will mark the subscription as suppressed by filling the column `suppressed_at` with the current timestamp.

#### Starting

To start a subscription, use the method `start()`:

```php
$subscriber->subscription->start(); // To start it immediately
$subscriber->subscription->start($startDate); // To determine when to start
```

This method will mark the subscription as started (or scheduled to start) by filling the column `started_at`.

### Feature Consumption

To register a consumption of a given feature, you just have to call the `consume` method and pass the feature name and the consumption amount (you don't need to provide it for not consumable features):

```php
$subscriber->consume('deploy-minutes', 4.5);
```

The method will check if the feature is available and throws exceptions if they are not: `OutOfBoundsException` if the feature is not available to the plan, and `OverflowException` if it is available, but the charges are not enough to cover the consumption.

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

- [Lucas Vinicius](https://github.com/lucasdotdev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
