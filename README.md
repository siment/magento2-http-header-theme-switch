# siment/module-http-header-theme-switch

Magento 2 module that enables automatic theme switching based on X-UA-Device header.
(Or any other header)

## What does this module do?

This module makes sure that Magento's 
[design exceptions](http://devdocs.magento.com/guides/v2.0/frontend-dev-guide/themes/theme-apply.html#theme-apply-except)
logic checks the value of the header "X-UA-Device" in addition to "User-Agent" when 
determining what theme it should use. You can also configure it to listen to other
headers than the pre-configured "X-UA-Device" header.

## Why this module?

### Short version

Because many web proxies, like [Varnish](https://varnish-cache.org/docs/trunk/users-guide/devicedetection.html), 
uses the "X-UA-Device" header for device detection and Magento should respect it. 

### Longer version

Magento 2 allows for theme switching based on 
[design exceptions](http://devdocs.magento.com/guides/v2.0/frontend-dev-guide/themes/theme-apply.html#theme-apply-except)
which enables "*you to specify an alternative theme for particular user-agents*". 

There are hundreds of user agents out there now and it makes it challenging to 
correctly identify which agents are coming from mobile, tablet and desktop
devices.

There are great libraries for making detection easier - like the generic 
[mobiledetect/mobiledetectlib](https://github.com/serbanghita/Mobile-Detect)
and the excellent Magento module 
[eadesignro/module-mobiledetect](https://github.com/EaDesgin/magento2-mobiledetect).
Those modules will not work out of the box if you have a web proxy like Varnish
installed.

That is why I wanted to use the [Varnish Mobile Detect](https://github.com/willemk/varnish-mobiletranslate)
module which sends the "X-UA-Device" header from Varnish for device detection 
rather than tampering with the "User-Agent" header.

## How to install

In Magento root:

```bash
$ composer config repositories.module-http-header-theme-switch git https://github.com/siment/module-http-header-theme-switch.git
$ composer require siment/module-http-header-theme-switch
$ php bin/magento module:enable Siment_HttpHeaderThemeSwitch
$ php bin/magento cache:clean
```

## Configuration

In Magento admin:

**Stores** -> (Settings) **Configuration** -> **General** -> **Design** 
-> **HTTP Header Theme Switch** -> **HTTP Header**:

HTTP header which will be matched for theme exceptions in addition to the standard header "User-Agent".
Standard value is "*HTTP_X_UA_DEVICE*"

You define the matching rules under **Content** -> (Design) **Configuration** 
-> (Select a theme) **Edit** -> **User Agent Rules**

## How to test

Make sure the Composer dependencies have been installed and that the "post-install-cmd" 
scripts have executed.

In module directory:

```bash
$ composer install
$ composer run-script post-install-cmd          # May not be necessary
$ vendor/bin/phpunit                            # For unit tests
$ vendor/bin/phpcs                              # For code sniffs
$ vendor/bin/phpmd src/,Test/ text phpmd.xml    # For mess detector
```

## How to contribute

Create an issue or a pull request.
 
## License 

 * Licensed under Academic Free License ("AFL") v. 3.0
 * See LICENSE.txt or [https://opensource.org/licenses/afl-3.0](https://opensource.org/licenses/afl-3.0)