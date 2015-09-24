## Twig integration for Thelia

This module use [Twig](http://twig.sensiolabs.org) template engine as parser for Thelia and replace Smarty.

**This module is not stable and is still in development. See the RoadMap if you want to know which features are missing**

###Summary :

* [Installation](#installation)
* [Activation](#activation)
* [Usage](#usage)
* [Syntax](#syntax)
    * [Loop](#loop)
    * [Conditional loop](#conditional-loop)
    * [Paginated loop](#paginated-loop)
    * [URL management](#url-management)
    * [Translation](#translation)
    * [Security](#security)
    * [Data access functions](#data-access-functions)
    * [Cart postage](#cart-postage)
    * [Format functions](#format-functions)
    * [Flash messages](#flash-messages)
    * [Hooks](#hooks)
* [Add your own twig extension](#how-to-add-your-own-extension)
* [Roadmap](#roadmap)

### Installation

You can only install this module with composer :

```
$ composer require thelia/twig-module:dev-master
```

### Activation

It is required to enable this module with the cli tools and then disable TheliaSmarty module :

```
$ php Thelia module:refresh
$ php Thelia module:activate TheliaTwig
$ php Thelia module:deactivate TheliaSmarty
```

### Usage

Template files must be suffixed by ```.twig```, for example ```index.html.twig```

The template structure is the same as the actual structure, so you can referer to the actual [documentation](http://doc.thelia.net/en/documentation/templates/introduction.html#structure-of-a-template)

You can test the module with this module : <https://github.com/bibich/TheliaTwigTest>

### Syntax

#### Loop

loop feature is a Twig tag, you have to use it like a block. All loop's parameters use [literals](http://twig.sensiolabs.org/doc/templates.html#literals) syntax and are the same as the acutal parameters.
The tag start with ```loop``` and finish with ```endloop```

**example** :

```
<ul>
{% loop {type:"category", name:"cat", limit:"2"} %}
    <li>{{ ID }} : {{ TITLE }}
        <ul>
    {% loop {type:"product", name:"prod", category: ID} %}
        <li>Title : {{ TITLE }} </li>
    {% endloop %}
        </ul>
    </li>
{% endloop %}
</ul>
```

#### Conditional loop

Conditional loops are implemented. As for Smarty a ```ifloop``` can wrap a ```loop``` and can be used after the related loop.
```elseloop``` must be used after the related ```loop```

```
{% ifloop {rel:"cat"} %}
    <p>Before categories</p>
    <ul>
    {% loop {type:"category", name:"cat", limit:"2"} %}
        <li>{{ ID }} : {{ TITLE }}
            <ul>
        {% loop {type:"product", name:"prod", category: ID} %}
            <li>Title : {{ TITLE }} </li>
        {% endloop %}
            </ul>
        </li>
    {% endloop %}
    </ul>
<p>After categories</p>
{% endifloop %}
{% elseloop {rel:"cat"} %}
    <p>there is no category</p>
{% endelseloop %}
```

#### Paginated loop

Paginated loop works exactly like paginated loop for Smarty, just the syntax change. See the official documentation for
all parameters : http://doc.thelia.net/en/documentation/loop/index.html#page-loop

Syntax example :

```
<p>Products Loop</p>
<ul>
{% loop {type:"product", name:"pagination", limit:"5", page:"3"} %}
    <li>{{ TITLE }}</li>
{% endloop %}
</ul>

<p>Pagination</p>
<ul>
{% pageloop {rel: "pagination"} %}
    <li>{{ PAGE }} {% if CURRENT == PAGE %} current {% endif %} / last : {{ END }}</li>
{% endpageloop %}
</ul>
```

### Url management

#### url

url is a function. It generates an absolute url for a given path or file.

```
url($path, $parameters = array(), $current = false, $file = null, $noAmp = false, $target = null)
```

parameters :

Parameters | Description | example
--- | --- | ---
path | The value of the path parameter is the route path you want to get as an URL | ```url("/product/")```
file | The value of the file parameter is the absolute path (from /web) of a real file, that will be served by your web server, and not processed by Thelia | ```url(null,[], false, "file.pdf")```
parameters | paremeters added to the query string | ```url("/product" ,{arg1:"val1", arg2:"val2"})```
current | generate absolute URL grom the current URL | ```url(null ,[], true)```
noAmp | escape all ```&``` as ```&amp;``` that may be present in the generated URL. | ```url("/product" ,[], false, null, true)```
target | Add an anchor to the URL | ```url("/product" ,[], false, null, false, "cart")```

Complete example :

```
<p>
    <a href="{{ url("/product/", {id: 2, arg1: "val1"}) }}">my link</a>
</p>
```

generated link : http://domain.tld?id=2&arg1=val1

#### url_token

same as ```url``` function. This function just add a token paremeter in the url to prevent CSRF security issue.

**example** :

```
<a href="{{ url_token("/product/", {id: 2, arg1: "val1"}) }}">my tokenized link</a>
```

generated link : http://domain.tld?id=2&arg1=val1&_token=UniqueToken

#### current_url

return the current url

```
current_url()
```

**example** :

```
<a href="{{ current_url() }}">current link</a>
```

#### previous_url

return the previous url saved in session

```
previous_url
```

**example** :

```
<a href="{{ previous_url() }}">previous link</a>
```

#### index_url

return the homepage url

```
index_url()
```

**example** :

```
<a href="{{ index_url() }}">index link</a>
```

### Translation

#### default_domain

default_domain is a tag for defining the default translation domain. If defined you don't need to specify it when you want to translation a string in the current template.

**Usage** :

```
{% default_domain "fo.default" %}
```

#### default_locale

tag for defining a locale and don't use the locale stored in session.

**Usage** :

```
{% default_locale "fr_FR" %}
```

#### intl

function for string translation

```
intl($id, $parameters = [], $domain = null, $locale = null)
```

parameters :

Parameters | Description | Example
--- | --- | ---
id | the string to translate | ```intl('secure payment')```
parameters | variable use if a placeholder is used in the string to translate | ```intl('secure payment %payment', {'%payment' => 'atos'})``` => secure payment atos
domain | message domain, will override domain defined with tag ```default_domain``` | ```{{ intl('secure payment', [], 'front') }}```
locale | specific locale to use for this translation. Will override locale defined with tag ```default_locale``` and the locale defined in session | ```{{ intl('Secure payment', [], null, 'en_US') }}```

**Complete example** :

```
{% default_domain "fo.default" %}
{% default_locale "fr_FR" %}
<p>
    translation : {{ intl('Secure payment', [], null, 'en_US') }} <br>
    translation 2 : {{ intl('Secure payment') }} <br>
    translation 3 : {{ intl('Sorry, an error occurred: %error', {'%error': 'foo'}, 'front') }} <br>
</p>
```

### Security

#### auth

tag checking if a user has access granted.

example :

```
{% auth {role: "CUSTOMER", login_tpl:"login"} %}
```

Parameters :

Parameters | Description
--- | ---
role | In Thelia 2, a user can only have one of these two roles: ADMIN and/or CUSTOMER
resource | if a user can access to a specific resource. See : http://doc.thelia.net/en/documentation/templates/security.html#resource
module | Name of the module(s) which the user must have access
access | access mode : CREATE, VIEW, UPDATE, DELETE
login_tpl |This argument is the name of the view name (the login page is "login"). If the user is not granted and this argument is defined, it redirects to this view.

#### check_cart_not_empty

This tag checks if the customer's cart is empty, and redirects to the route "cart.view" if it is.

```
{% check_cart_not_empty %}
```

#### check_valid_delivery

Check if the delivery module and address are valid, redirects to the route "order.delivery" if not.

```
{% check_valid_delivery %}
```

### data access functions

All data access function allow to access to a specific property of an entity. For some of them through an id present in the query string, for others through
data saved in session

#### admin

Provides access to the current logged administrator attributes using the accessors.

```
<p>
    admin firstname : {{ admin('firstname') }}
</p>
```

#### brand

Provides access to an attribute of the current brand (brand_id parameter in the query string). If the product_id is in the query string,
the brand function will find the brand linked to the product.

```
<p>
    brand title : {{ brand('title') }}
</p>
```

#### cart

list of implemented parameters :

* count_product : number of products in the current cart
* count_item : addition off all quantity for each products
* total_price : total amount without taxes
* total_taxed_price : total amount with taxes
* total_taxed_price_without_discount : total amount with taxes and without discount
* is_virtual : if cart contains or not virutal products
* total_vat : tax amount

example :

```
<p>
    number of products : {{ cart('count_product') }}
</p>
```

#### category

Provides access to an attribute of the current category (category_id parameter is the query string). If the product_id is in the query string,
the default category linked to this product is used.

```
<p>
    Category title : {{ category('title') }}
</p>
```

#### config

return the value of a wanted configuration key. Default as second parameter if the key does not exists.

```
<p>
    default front office template : {{ config('active-front-template', 'default') }}
</p>
```

#### content

Provides access to an attribute of the current content (content_id in the query string).

```
<p>
    content title : {{ content('title') }}
</p>
```

#### country

Provides access to an attribute for the default country

```
<p>
    iso alpha2 code : {{ country('isoalpha2') }}
</p>
```

#### currency

Provides access to an attribute of the current currency (saved in session)

```
<p>
    currency symbol : {{ currency('symbol') }}
</p>
```

#### customer

Provides access to an attribute of the logged customer

```
<p>
    customer first name : {{ customer('firstname') }}
</p>
```

#### folder

 Provides access to an attribute of the current folder (folder_id in the query string). If the content_id parameter is in the query string,
 the default linked folder will be used.

 ```
 <p>
    folder title : {{ folder('title') }}
 </p>
 ```

#### lang

Provides access to an attribute of the current lang saved in session

```
<p>
    locale : {{ lang('locale') }}
</p>
```

#### order

Provides access to an attribute of the current order

list of implemented parameters :

* untaxed_postage : postage cost without taxes
* postage : postage cost
* postage_tax : postage tax amount
* discount : discount amount
* delivery_address : delivery address id
* invoice_address : invoice address id
* delivery_module : delivery module id
* payment_module : payment module id
* has_virtual_product : if order contains at least one virtual product

example :

```
<p>
    discount amount : {{ order('discount') }}
</p>
```

#### product

Provides access to an attribute of the current product (product_id parameter in query string)

```
<p>
    product title : {{ product('title') }}
</p>
```

### Cart postage

retrieves the postage amount of the current cart if it exists.

Thelia uses the following rules to select the country :

* the country of the delivery address of the customer related to the cart if it exists
* the country saved in cookie if customer have changed the default country
* the default country for the shop if it exists

Inside the ```postage``` block this variables are defined :

* country_id: the country id or null
* delivery_id: the delivery id or null
* postage: the postage amount or 0.0
* is_customizable: indicate if the postage can be customized. False When customer is signed and have a valid delivery address

```
{% postage %}
    postage : {{ postage ~ currency('symbol') }}
{% endpostage %}
```

### format functions

#### format_date

return date in expected format

available parameters :

* params => Array :
    * date : `DateTime` object (mandatory if timestamp is not present)
    * timestamp : a Unix timestamp (mandatory if date is not present)
    * format => will output the format with specific format (see date() function)
    * output => list of default system format. Values available :
        * date => date format
        * time => time format
        * datetime => datetime format (default)
    * locale => format the date according to a specific locale (eg. fr_FR)

```twig
{% set myDate = date() %}
{# format the date in datetime format for the current locale #}
{{ format_date({date: myDate}) }}
{# format the date in date format for the current locale #}
{{ format_date({date: myDate, output:"date"}) }}
{# format the date with a specific format (with the default locale on your system) #}
{{ format_date({date: myDate, format:"Y-m-d H:i:s"}) }}
{# format the date with a specific format with a specific locale #}
{{ format_date({date: myDate, format:"D l F j", locale:"en_US"}) }}
{{ format_date({date: myDate, format:"l F j", locale:"fr_FR"}) }}
{# using a timestamp instead of a date #}
{{ format_date({timestamp: myDate|date('U'), output:"datetime"}) }}
```

#### format_number

return numbers in expected format

available parameters :

* params => Array :
    * number => int or float number (mandatory)
    * decimals => how many decimals format expected
    * dec_point => separator for the decimal point
    * thousands_sep => thousands separator

```twig
{# specific format #}
{{ format_number({number:"1246.12", decimals:"1", dec_point:",", thousands_sep:" "}) }}
{# format for the current locale #}
{{ format_number({number:"1246.12"}) }}
```

#### format_money

return money in expected format

available parameters :

* params => Array :
    * number => int or float number (mandatory)
    * decimals => how many decimals format expected
    * dec_point => separator for the decimal point
    * thousands_sep => thousands separator
    * symbol => Currency symbol

```twig
{#  will output "1 246,1 €" #}
{{ format_number({number:"1246.12", decimals:"1", dec_point:",", thousands_sep:" ", symbol:"€"}) }}
```

### flash messages

#### has_flash

Test if message exists for the given type.

available parameters :

* type (mandatory)

```twig
{% if has_flash('test') %}
    {# do something #}
{% endif %}
```

#### flash

Get all messages or messages for the given type. After the call of the function
flash messages are deleted.

available parameter :

* type : a specific type (string or null)
    * if provided, get all messages for the given type and return an array of messages
    * if not provided, get all flash messages. It will return an array. The key
    will be the type and the value an array of associated messages

```twig
{% if has_flash('notice') %}
    <div class="alert alert-notice">
    {% for message in flash('notice') %}
        {{ message }}<br>
    {%  endfor %}
    </div>
{% endif %}

{% for type, messages in flash() %}
    <div class="alert alert-{{ type }}">
    {% for message in messages %}
        {{ message }}<br>
    {%  endfor %}
    </div>
{%  endfor %}
```
### Hooks

#### `hook` tags

The tag ```hook``` allows you to get the content related to a specific hook
specified by its name.

available parameters :

* params => Array :
    * name => the hook name (mandatory)
    * ... You can add as many parameters as you want. they will be available in
        the hook event

```twig
{% hook {name: "hook_code", var1: "value1", var2: "value2", ... } %}
```

#### `hookblock` and `forhook` tags

The tag ```hookblock``` allows you to get the content related to a specific hook
specified by its name. The content is not injected directly, but has to be
manipulated by a `forhook` tag.

available parameters :

* params => Array :
    * name => the hook name (mandatory)
    * fields => indicates the fields that you can add to the `hookblock` event
    * ... You can add as many parameters as you want. they will be available in
        the hook event

The tag ```forhook``` iterates on the results of a `hookblock` tag. You should
set the `rel` attribute to establish the link. *You can use the `forhook` multiple
times*.

```twig
{% hookblock {name: "hookblock_code", fields: "id,title, content", var1: "value1", ... } %}
{% forhook {rel: 'hookblock_code'} %}
    <div id="{{ id }}">
        <h2>{{ title }}</h2>
        <p>{{ content|raw }}</p>
    </div>
{% endforhook %}
```

#### `ifhook` and `elsehook` tags

These tags will test if `hook` or `hookblock` are empty or not.

```twig
{% ifhook {rel:"main.content-bottom"} %}
    {# displayed if main.content-bottom is not empty #}
    <hr class="space">
    {% hook {name: "main.content-bottom"} %}
    <hr class="space">
{% endifhook %}

{% elsehook {rel:"main.content-bottom"} %}
    {# displayed if main.content-bottom is empty #}
    <p><a href="#top">Back to top</a></p>
{% endelsehook %}

```

### How to add your own extension

The tag ```thelia.parser.add_extension``` allows you to add your own twig extension.

**example** :

```
<service id="thelia.parser.loop_extension" class="TheliaTwig\Template\Extension\Loop">
    <argument type="service" id="thelia.parser.loop_handler" />
    <tag name="thelia.parser.add_extension" />
</service>
```

### Roadmap

* ~~loop~~
* ~~conditional loop~~
* ~~paginated loop~~
* Assetic integration
* ~~I18n support~~
* Form support
* ~~URL management~~
    * ~~url function~~
    * ~~token_url function~~
    * ~~navigate function~~
    * ~~set_previous_url function~~
* Hook support
* ~~date and money format~~
* ~~Flash messages~~
* ~~cart postage~~
* ~~DataAccessFunction~~
    * ~~admin~~
    * ~~brand~~
    * ~~cart~~
    * ~~category~~
    * ~~config~~
    * ~~content~~
    * ~~country~~
    * ~~currency~~
    * ~~customer~~
    * ~~folder~~
    * ~~lang~~
    * ~~order~~
    * ~~product~~
* ~~Security~~
    * ~~checkAuth~~
    * ~~checkCartNotEmpty~~
    * ~~checkValidDelivery~~
