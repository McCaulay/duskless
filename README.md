<p align="center"><img src="https://laravel.com/assets/img/components/logo-dusk.svg"></p>

<p align="center">
<a href="https://packagist.org/packages/mccaulay/duskless"><img src="https://poser.pugx.org/mccaulay/duskless/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/mccaulay/duskless"><img src="https://poser.pugx.org/mccaulay/duskless/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/mccaulay/duskless"><img src="https://poser.pugx.org/mccaulay/duskless/license.svg" alt="License"></a>
</p>

# Laravel Duskless

- [Introduction](#introduction)
- [Installation](#installation)
    - [Managing ChromeDriver Installations](#managing-chromedriver-installations)
- [Getting Started](#getting-started)
    - [Creating Browsers](#creating-browsers)
- [Interacting With Elements](#interacting-with-elements)
    - [Duskless Selectors](#duskless-selectors)
    - [Clicking Links](#clicking-links)
    - [Text, Values, & Attributes](#text-values-and-attributes)
    - [Using Forms](#using-forms)
    - [Attaching Files](#attaching-files)
    - [Using The Keyboard](#using-the-keyboard)
    - [Using The Mouse](#using-the-mouse)
    - [JavaScript Dialogs](#javascript-dialogs)
    - [Scoping Selectors](#scoping-selectors)
    - [Waiting For Elements](#waiting-for-elements)
- [Pages](#pages)
    - [Generating Pages](#generating-pages)
    - [Configuring Pages](#configuring-pages)
    - [Navigating To Pages](#navigating-to-pages)
    - [Shorthand Selectors](#shorthand-selectors)
    - [Page Methods](#page-methods)
- [Components](#components)
    - [Generating Components](#generating-components)
    - [Using Components](#using-components)  

<a name="introduction"></a>
## Introduction

Laravel Duskless provides an expressive, easy-to-use browser automation API. By default, Duskless does not require you to install JDK or Selenium on your machine. Instead, Duskless uses a standalone [ChromeDriver](https://sites.google.com/a/chromium.org/chromedriver/home) installation. However, you are free to utilize any other Selenium compatible driver you wish.

<a name="installation"></a>
## Installation

To get started, you should add the `mccaulay/duskless` Composer dependency to your project:

    composer require mccaulay/duskless

> {note} You can register Duskless in production as all of the issues with Laravel Duskless that prevent you from doing so are removed. Such as the loginAs method.

After installing the Duskless package, run the `duskless:install` Artisan command:

    php artisan duskless:install

A `Browser` directory will be created within your `app` directory and will contain an example page. Next, set the `APP_URL` environment variable in your `.env` file. This value should match the URL you use to access your application in a browser.

<a name="managing-chromedriver-installations"></a>
### Managing ChromeDriver Installations

If you would like to install a different version of ChromeDriver than what is included with Laravel Duskless, you may use the `duskless:chrome-driver` command:

    # Install the latest version of ChromeDriver for your OS...
    php artisan duskless:chrome-driver

    # Install a given version of ChromeDriver for your OS...
    php artisan duskless:chrome-driver 74

    # Install a given version of ChromeDriver for all supported OSs...
    php artisan duskless:chrome-driver --all

> {note} Duskless requires the `chromedriver` binaries to be executable. If you're having problems running Duskless, you should ensure the binaries are executable using the following command: `chmod -R 0755 vendor/laravel/dusk/bin/`.

<a name="getting-started"></a>
## Getting Started

<a name="creating-browsers"></a>
### Creating Browsers

To get started, let's write an example controller that visits the github repository. To create a browser instance, call the `browse` method:

```php
    <?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use McCaulay\Duskless\Duskless;

    class ExampleController extends Controller
    {
        /**
        * Browse a website example.
        *
        * @return \Illuminate\Http\Response
        */
        public function example()
        {
            $duskless = new Duskless();

            // Set the window size to 1080p
            $duskless->windowSize(1920, 1080);

            // Set headless and without gpu
            // $duskless->headless()->disableGpu()->noSandbox();

            // Set user agent
            $duskless->userAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');

            // Start the browser
            $duskless->start();

            // Launch a browser
            $duskless->browse(function ($browser) {

                // Visit web page
                $browser->visit('https://github.com/McCaulay/duskless');
            });

            return view('example.index');
        }
    }
```

As you can see in the example above, the `browse` method accepts a callback. A browser instance will automatically be passed to this callback by Duskless and is the main object used to interact with and make assertions against your application.

#### Creating Multiple Browsers

Sometimes you may need multiple browsers when automating a browser. For example, multiple browsers may be needed to chat between two screens that interacts with websockets. To create multiple browsers, "ask" for more than one browser in the signature of the callback given to the `browse` method:

```php
    $this->browse(function ($first, $second) {
        $first->visit('/home')
              ->waitForText('Message');

        $second->visit('/home')
               ->waitForText('Message')
               ->type('message', 'Hey McCaulay')
               ->press('Send');

        $first->waitForText('Hey McCaulay');
    });
```

#### Resizing Browser Windows

You may use the `resize` method to adjust the size of the browser window:

```php
    $browser->resize(1920, 1080);
```

The `maximize` method may be used to maximize the browser window:

```php
    $browser->maximize();
```

<a name="interacting-with-elements"></a>
## Interacting With Elements

<a name="duskless-selectors"></a>
### Duskless Selectors

To define a selector, add a `dusk` attribute to your HTML element. Then, prefix the selector with `@` to manipulate the attached element:

```php
    // HTML...

    <button dusk="login-button">Login</button>

    // Test...

    $browser->click('@login-button');
```

<a name="clicking-links"></a>
### Clicking Links

To click a link, you may use the `clickLink` method on the browser instance. The `clickLink` method will click the link that has the given display text:

```php
    $browser->clickLink($linkText);
```

> {note} This method interacts with jQuery. If jQuery is not available on the page, Duskless will automatically inject it into the page so it is available for the test's duration.

<a name="text-values-and-attributes"></a>
### Text, Values, & Attributes

#### Retrieving & Setting Values

Duskless provides several methods for interacting with the current display text, value, and attributes of elements on the page. For example, to get the "value" of an element that matches a given selector, use the `value` method:

```php
    // Retrieve the value...
    $value = $browser->value('selector');

    // Set the value...
    $browser->value('selector', 'value');
```

#### Retrieving Text

The `text` method may be used to retrieve the display text of an element that matches the given selector:

```php
    $text = $browser->text('selector');
```

#### Retrieving Attributes

Finally, the `attribute` method may be used to retrieve an attribute of an element matching the given selector:

```php
    $attribute = $browser->attribute('selector', 'value');
```

<a name="using-forms"></a>
### Using Forms

#### Typing Values

Duskless provides a variety of methods for interacting with forms and input elements. First, let's take a look at an example of typing text into an input field:

```php
    $browser->type('email', 'taylor@laravel.com');
```

Note that, although the method accepts one if necessary, we are not required to pass a CSS selector into the `type` method. If a CSS selector is not provided, Duskless will search for an input field with the given `name` attribute. Finally, Duskless will attempt to find a `textarea` with the given `name` attribute.

To append text to a field without clearing its content, you may use the `append` method:

```php
    $browser->type('tags', 'foo')
            ->append('tags', ', bar, baz');
```

You may clear the value of an input using the `clear` method:

```php
    $browser->clear('email');
```

#### Dropdowns

To select a value in a dropdown selection box, you may use the `select` method. Like the `type` method, the `select` method does not require a full CSS selector. When passing a value to the `select` method, you should pass the underlying option value instead of the display text:

```php
    $browser->select('size', 'Large');
```

You may select a random option by omitting the second parameter:

```php
    $browser->select('size');
```

#### Checkboxes

To "check" a checkbox field, you may use the `check` method. Like many other input related methods, a full CSS selector is not required. If an exact selector match can't be found, Duskless will search for a checkbox with a matching `name` attribute:

```php
    $browser->check('terms');

    $browser->uncheck('terms');
```

#### Radio Buttons

To "select" a radio button option, you may use the `radio` method. Like many other input related methods, a full CSS selector is not required. If an exact selector match can't be found, Duskless will search for a radio with matching `name` and `value` attributes:

```php
    $browser->radio('version', 'php7');
```

<a name="attaching-files"></a>
### Attaching Files

The `attach` method may be used to attach a file to a `file` input element. Like many other input related methods, a full CSS selector is not required. If an exact selector match can't be found, Duskless will search for a file input with matching `name` attribute:

```php
    $browser->attach('photo', __DIR__.'/photos/me.png');
```

> {note} The attach function requires the `Zip` PHP extension to be installed and enabled on your server.

<a name="using-the-keyboard"></a>
### Using The Keyboard

The `keys` method allows you to provide more complex input sequences to a given element than normally allowed by the `type` method. For example, you may hold modifier keys entering values. In this example, the `shift` key will be held while `taylor` is entered into the element matching the given selector. After `taylor` is typed, `otwell` will be typed without any modifier keys:

```php
    $browser->keys('selector', ['{shift}', 'taylor'], 'otwell');
```

You may even send a "hot key" to the primary CSS selector that contains your application:

```php
    $browser->keys('.app', ['{command}', 'j']);
```

> {tip} All modifier keys are wrapped in `{}` characters, and match the constants defined in the `Facebook\WebDriver\WebDriverKeys` class, which can be [found on GitHub](https://github.com/facebook/php-webdriver/blob/community/lib/WebDriverKeys.php).

<a name="using-the-mouse"></a>
### Using The Mouse

#### Clicking On Elements

The `click` method may be used to "click" on an element matching the given selector:

```php
    $browser->click('.selector');
```

#### Mouseover

The `mouseover` method may be used when you need to move the mouse over an element matching the given selector:

```php
    $browser->mouseover('.selector');
```

#### Drag & Drop

The `drag` method may be used to drag an element matching the given selector to another element:

```php
    $browser->drag('.from-selector', '.to-selector');
```

Or, you may drag an element in a single direction:

```php
    $browser->dragLeft('.selector', 10);
    $browser->dragRight('.selector', 10);
    $browser->dragUp('.selector', 10);
    $browser->dragDown('.selector', 10);
```

<a name="javascript-dialogs"></a>
### JavaScript Dialogs

Duskless provides various methods to interact with JavaScript Dialogs:

```php
    // Wait for a dialog to appear:
    $browser->waitForDialog($seconds = null);
    
    // Assert that a dialog has been displayed and that its message matches the given value:
    $browser->assertDialogOpened('value');

    // Type the given value in an open JavaScript prompt dialog:
    $browser->typeInDialog('Hello World');
```

To close an opened JavaScript Dialog, clicking the OK button:

```php
    $browser->acceptDialog();
```

To close an opened JavaScript Dialog, clicking the Cancel button (for a confirmation dialog only):

```php
    $browser->dismissDialog();
```

<a name="scoping-selectors"></a>
### Scoping Selectors

Sometimes you may wish to perform several operations while scoping all of the operations within a given selector. For example, you may wish to assert that some text exists only within a table and then click a button within that table. You may use the `with` method to accomplish this. All operations performed within the callback given to the `with` method will be scoped to the original selector:

```php
    $browser->with('.table', function ($table) {
        $table->assertSee('Hello World')
              ->clickLink('Delete');
    });
```

<a name="waiting-for-elements"></a>
### Waiting For Elements

Using a variety of methods, you may wait for elements to be visible on the page or even wait until a given JavaScript expression evaluates to `true`.

#### Waiting

If you need to pause the test for a given number of milliseconds, use the `pause` method:

```php
    $browser->pause(1000);
```

#### Waiting For Selectors

The `waitFor` method may be used to pause the execution of the test until the element matching the given CSS selector is displayed on the page. By default, this will pause the script for a maximum of five seconds before throwing an exception. If necessary, you may pass a custom timeout threshold as the second argument to the method:

```php
    // Wait a maximum of five seconds for the selector...
    $browser->waitFor('.selector');

    // Wait a maximum of one second for the selector...
    $browser->waitFor('.selector', 1);
```

You may also wait until the given selector is missing from the page:

```php
    $browser->waitUntilMissing('.selector');

    $browser->waitUntilMissing('.selector', 1);
```

#### Scoping Selectors When Available

Occasionally, you may wish to wait for a given selector and then interact with the element matching the selector. For example, you may wish to wait until a modal window is available and then press the "OK" button within the modal. The `whenAvailable` method may be used in this case. All element operations performed within the given callback will be scoped to the original selector:

```php
    $browser->whenAvailable('.modal', function ($modal) {
        $modal->assertSee('Hello World')
              ->press('OK');
    });
```

#### Waiting For Text

The `waitForText` method may be used to wait until the given text is displayed on the page:

```php
    // Wait a maximum of five seconds for the text...
    $browser->waitForText('Hello World');

    // Wait a maximum of one second for the text...
    $browser->waitForText('Hello World', 1);
```

#### Waiting For Links

The `waitForLink` method may be used to wait until the given link text is displayed on the page:

```php
    // Wait a maximum of five seconds for the link...
    $browser->waitForLink('Create');

    // Wait a maximum of one second for the link...
    $browser->waitForLink('Create', 1);
```

#### Waiting On The Page Location

When making a path assertion such as `$browser->assertPathIs('/home')`, the assertion can fail if `window.location.pathname` is being updated asynchronously. You may use the `waitForLocation` method to wait for the location to be a given value:

```php
    $browser->waitForLocation('/secret');
```

You may also wait for a named route's location:

```php
    $browser->waitForRoute($routeName, $parameters);
```

#### Waiting for Page Reloads

If you need to wait after a page has been reloaded, use the `waitForReload` method:

```php
    $browser->click('.some-action')
            ->waitForReload();
```

#### Waiting On JavaScript Expressions

Sometimes you may wish to pause the execution of a script until a given JavaScript expression evaluates to `true`. You may easily accomplish this using the `waitUntil` method. When passing an expression to this method, you do not need to include the `return` keyword or an ending semi-colon:

```php
    // Wait a maximum of five seconds for the expression to be true...
    $browser->waitUntil('App.dataLoaded');

    $browser->waitUntil('App.data.servers.length > 0');

    // Wait a maximum of one second for the expression to be true...
    $browser->waitUntil('App.data.servers.length > 0', 1);
```

#### Waiting On Vue Expressions

The following methods may be used to wait until a given Vue component attribute has a given value:

```php
    // Wait until the component attribute contains the given value...
    $browser->waitUntilVue('user.name', 'McCaulay', '@user');

    // Wait until the component attribute doesn't contain the given value...
    $browser->waitUntilVueIsNot('user.name', null, '@user');
```

#### Waiting With A Callback

Many of the "wait" methods in Duskless rely on the underlying `waitUsing` method. You may use this method directly to wait for a given callback to return `true`. The `waitUsing` method accepts the maximum number of seconds to wait, the interval at which the Closure should be evaluated, the Closure, and an optional failure message:

```php
    $browser->waitUsing(10, 1, function () use ($something) {
        return $something->isReady();
    }, "Something wasn't ready in time.");
```

<a name="pages"></a>
## Pages

Pages allow you to define expressive actions that may then be performed on a given page using a single method. Pages also allow you to define short-cuts to common selectors for your application or a single page.

<a name="generating-pages"></a>
### Generating Pages

To generate a page object, use the `duskless:page` Artisan command. All page objects will be placed in the `app/Browser/Pages` directory:

    php artisan duskless:page Login

<a name="configuring-pages"></a>
### Configuring Pages

By default, pages have two methods: `url` and `elements`. We will discuss the `url` method now. The `elements` method will be [discussed in more detail below](#shorthand-selectors).

#### The `url` Method

The `url` method should return the path of the URL that represents the page. Duskless will use this URL when navigating to the page in the browser:

```php
    /**
     * Get the URL for the page.
     *
     * @return string
     */
    public function url()
    {
        return '/login';
    }
```

<a name="navigating-to-pages"></a>
### Navigating To Pages

Once a page has been configured, you may navigate to it using the `visit` method:

```php
    use App\Browser\Pages\Login;

    $browser->visit(new Login);
```

Sometimes you may already be on a given page and need to "load" the page's selectors and methods into the current context. This is common when pressing a button and being redirected to a given page without explicitly navigating to it. In this situation, you may use the `on` method to load the page:

```php
    use App\Browser\Pages\CreatePlaylist;

    $browser->visit('/dashboard')
            ->clickLink('Create Playlist')
            ->on(new CreatePlaylist);
```

<a name="shorthand-selectors"></a>
### Shorthand Selectors

The `elements` method of pages allows you to define quick, easy-to-remember shortcuts for any CSS selector on your page. For example, let's define a shortcut for the "email" input field of the application's login page:

```php
    /**
     * Get the element shortcuts for the page.
     *
     * @return array
     */
    public function elements()
    {
        return [
            '@email' => 'input[name=email]',
        ];
    }
```

Now, you may use this shorthand selector anywhere you would use a full CSS selector:

```php
    $browser->type('@email', 'taylor@laravel.com');
```

#### Global Shorthand Selectors

After installing Duskless, a base `Page` class will be placed in your `app/Browser/Pages` directory. This class contains a `siteElements` method which may be used to define global shorthand selectors that should be available on every page throughout your application:

```php
    /**
     * Get the global element shortcuts for the site.
     *
     * @return array
     */
    public static function siteElements()
    {
        return [
            '@element' => '#selector',
        ];
    }
```

<a name="page-methods"></a>
### Page Methods

In addition to the default methods defined on pages, you may define additional methods which may be used throughout your script. For example, let's imagine we are building a music management application. A common action for one page of the application might be to create a playlist. Instead of re-writing the logic to create a playlist in each test, you may define a `createPlaylist` method on a page class:

```php
    <?php

    namespace App\Browser\Pages;

    use McCaulay\Duskless\Browser;

    class Dashboard extends Page
    {
        // Other page methods...

        /**
         * Create a new playlist.
         *
         * @param  \McCaulay\Duskless\Browser  $browser
         * @param  string  $name
         * @return void
         */
        public function createPlaylist(Browser $browser, $name)
        {
            $browser->type('name', $name)
                    ->check('share')
                    ->press('Create Playlist');
        }
    }
```

Once the method has been defined, you may use it within any script that utilizes the page. The browser instance will automatically be passed to the page method:

```php
    use App\Browser\Pages\Dashboard;

    $browser->visit(new Dashboard)
            ->createPlaylist('My Playlist')
            ->assertSee('My Playlist');
```

<a name="components"></a>
## Components

Components are similar to Duskless’s “page objects”, but are intended for pieces of UI and functionality that are re-used throughout your application, such as a navigation bar or notification window. As such, components are not bound to specific URLs.

<a name="generating-components"></a>
### Generating Components

To generate a component, use the `duskless:component` Artisan command. New components are placed in the `app/Browser/Components` directory:

    php artisan duskless:component DatePicker

As shown above, a "date picker" is an example of a component that might exist throughout your application on a variety of pages. It can become cumbersome to manually write the browser automation logic to select a date in dozens of scripts throughout your code. Instead, we can define a Duskless component to represent the date picker, allowing us to encapsulate that logic within the component:

```php
    <?php

    namespace App\Browser\Components;

    use McCaulay\Duskless\Browser;
    use McCaulay\Duskless\Component as BaseComponent;

    class DatePicker extends BaseComponent
    {
        /**
         * Get the root selector for the component.
         *
         * @return string
         */
        public function selector()
        {
            return '.date-picker';
        }

        /**
         * Get the element shortcuts for the component.
         *
         * @return array
         */
        public function elements()
        {
            return [
                '@date-field' => 'input.datepicker-input',
                '@month-list' => 'div > div.datepicker-months',
                '@day-list' => 'div > div.datepicker-days',
            ];
        }

        /**
         * Select the given date.
         *
         * @param  \McCaulay\Duskless\Browser  $browser
         * @param  int  $month
         * @param  int  $day
         * @return void
         */
        public function selectDate($browser, $month, $day)
        {
            $browser->click('@date-field')
                    ->within('@month-list', function ($browser) use ($month) {
                        $browser->click($month);
                    })
                    ->within('@day-list', function ($browser) use ($day) {
                        $browser->click($day);
                    });
        }
    }
```

<a name="using-components"></a>
### Using Components

Once the component has been defined, we can easily select a date within the date picker from any test. And, if the logic necessary to select a date changes, we only need to update the component:

```php
$this->browse(function (Browser $browser) {
    $browser->visit('/')
        ->within(new DatePicker, function ($browser) {
            $browser->selectDate(1, 2018);
        });
});
```

## License

Laravel Duskless is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
