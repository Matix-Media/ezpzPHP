# ezpzPHP

A simple, easy extendable, lightweight PHP-Framework.

## Features

-   Page Templates (Views)
-   Component System (Controls)
-   Page System
-   MySQL Build Query (Hydrahon)
-   Simple Routing

---

# Getting Started

Getting started is very easy with ezpzPHP.

## Installation

Copy the repository in your destination folder  
`git clone https://github.com/Matix-Media/ezpzPHP`

## Serving ezpzPHP

Typically, you should use a web server such as Apache or Ngnix.

If you want to use the built in PHP web server during development, navigate to the `public` folder and run the following command:

```shell
php -S localhost:8000
```

## Directory Structure

After successfully installing ezpzPHP, you should take a glance around the project to familiarize yourself with the directory structure.

-   The `public` folder holds all the files wich are public available to the web. So you should configure your webserver properly.
-   In the `content` directory, you can find folders such as `controls`, `pages`, `routes` and `views`.
-   The `content/controls` folder contains all of your controls (Components). And you should not put your controls anywhere else, otherwise ezpzPHP can not locate the control.
-   The `content/pages` directory holds all pages. And like the controls, you should not put pages anywhere else.
-   The `content/views` folder contains all of your views (Page Templates). And the same rules apply as for the controls and pages
-   In the `content/routes` directory are you route definitions. You can have multiple files to specify your routes, in case you have a lot of them.

## Routing

To get started, lets create our first route. In ezpzPHP, specifying routes is super simple. Just go to the `content/routes` directory and open up the `routes.php` file and the following route to the bottom of the file:

```php
Route::add("/users", function() {
    echo 'Users!';
});
```

Now, if you navigate to `/users` in your web browser, you should see `Users!` shown as the response. WOW! You`ve created your first route!

Routes can also contain a regex if you want to catch a variable out of the URL. For example, we want to get the number out of `/number/2/bar`.

```php
Route::add("/number/([0-9]*)/bar", function ($var1) {
    echo $var1;
});
```

You should now see the number `2` displayed in your browser!  
The same works with strings.

## Creating a Page

Next, we will create our first page, where we can display our number better and provide some more styling. Pages are located in the `content/pages` folder and contain the PHP for your page. We're going to place one new page in this folder: `number.php`.

```html
<div class="main">
    <p>
        Your number is
        <?= $arguments["number"] ?>
    </p>
</div>
```

As you can see, in this file, we have a HTML element as our page wrapper. We also have specified some PHP to display the number, for that we use the arguments variable.

Now that we have our page, let's use it actually in our route. Instead od just echoing the number.

```php
Route::add("/number/([0-9]*)/bar", function ($var1) {
    Route::load_page("number.php", ["number" => $var1]);
});
```

Amazing! Now you have setup your very first page. Next, let's start creating a view.

## Creating a View

Next, we will create a simple view, to enhance the layout of the page. Views live in the `content/views` directory and contain the HTML of your application. We're going to place a new view in this directory: `default.html`.

```html
<html>
    <head>
        <!--
            [!VIEW_CONTROL]
            header_control=header.php
            footer_control=footer.php
            [!VIEW_CONTROL]
        -->
        <title>$(title)</title>
        $(head_content)
    </head>

    <body>
        $(header_content)
        <!---->
        $(body_content)
        <!---->
        $(footer_content)
    </body>
</html>
```

Some of the syntax probably looks quite strange to you. That's because we're using ezpzPHP's simple templating system.  
Between the two `[!VIEW_CONTROL]` tags, we can specify some default header and footer control elements.  
With the `$()` tags, you can specify the location of the page content.

Now that we have our views,, let's use it from our `/number` route.

```php
Route::add("/number/([0-9]*)/bar", function ($var1) {
    Route::load_view("default.html", "number.php", null, ["number" => $var1]);
});
```

Wonderful! As you can see, we are passing the arguments array at the fourth place in the function, so that our is still able to read the passed number.

In the third parameter, you can pass a title for the view.

```php
Route::load_view("default.html", "number.php", "This is my title!");
```

At the seventh parameter you can pass some plain text for the head of the HTML.

```php
$head = "<link rel='stylesheet' href='/resources/styles.css'>"
Route::load_view("default.html", "number.php", null, null, null, null, $head);
```

If you haven't specified a header and footer control in the view itself, you can pass it now using the fifth and sixth parameter.

```php
Route::load_view("default.html", "number.php", null, null, "header.php", "footer.php");
```

Now you have setup a simple view which you can use as base for other pages.

## Creating a Control

Now that we have create a page and a view, we should now create some controls for the header and the footer. Controls are placed in the `content/controls` folder. We will create our first control in that folder and name it `header.php`.

```html
<header>
    <h1>Your Website</h1>
    <pre>Today is <?= date("l") ?></pre>
</header>

<style>
    header {
        padding: 1rem;
        background: black;
        color: white;
    }

    header h1 {
        margin: 0;
    }
</style>
```

As you can see, the structure of pages and controls is very similar. We can use HTML and PHP to display the control. We also use some CSS to make it a little prettier.

Now, if we want to use this control for example in a page, we can simply call `Route::load_control`.

```html
<div class="main">
    <p>Some text here!</p>

    <?php Route::load_control("header.php"); ?>
</div>
```

We can also pass some arguments to the control, like we do with pages.

```php
Route::load_control("header.php", ["foo" => "bar"]);
```

The controls are also used by views, to specify a header and a footer.

```html
<html>
    <head>
        <!--
           [!VIEW_CONTROL]
           header_control=header.php
           footer_control=footer.php
           [!VIEW_CONTROL]
       -->
    </head>

    <body>
        $(header_content)
        <!---->
        $(footer_content)
    </body>
</html>
```

Which control is used is either specified in the view itself or when loading the view. When you specify it on the load, it's get overwritten by the specified controls in the view itself.

```php
Route::load_view("default.html", "number.php", null, null, "header.php", "footer.php");
```

## Handling resources

If you want some public available resources for you website, then you should use the `public/resources` directory for that.

## Query Builder

ezpzPHP uses the Hydrahon SQL Query Builder by clancats. In order to connect with your database open up the `public/index.php` file and paste the following with your credentials under the comment with `Database Setup`.

```php
DB::setup(new PDO("mysql:host=localhost;dbname=test", "username", "password"));
```

You can access the database now by using:

```php
DB::$DB
```

For more information about the Query Builder take a loot at the docs:  
https://clancats.io/hydrahon/master/ or  
https://github.com/ClanCats/Hydrahon

---

Copyright &copy; 2020 Matix Media, Inc.  
[MIT License](https://github.com/Matix-Media/ezpzPHP/blob/master/LICENSE)
