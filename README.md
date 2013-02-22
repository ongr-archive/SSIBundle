Crunch\SSIBundle [![Build Status](https://secure.travis-ci.org/CrunchPHP/SSIBundle.png)](http://travis-ci.org/CrunchPHP/SSIBundle)
============
SSI support for Symfony2 applications designed for use with nginx, but should work for others
as well. It includes a kernel proxy class for substituting the SSI-tags during development, or as fallback.

* [List of available packages at packagist.org](http://packagist.org/packages/crunch/ssi-bundle)
  (See also [Composer: Declaring dependencies](http://getcomposer.org/doc/00-intro.md#declaring-dependencies))

Installation
============

* Add the corresponding package to your `composer.json`. See the list linked above for available packages
* Register `Crunch\Bundle\SSIBundle\CrunchSSIBundle` within your `AppKernel`-class

Usage
=====
Within twig

    {{ render('/path/to/interal', {strategy: 'ssi'}) }}

To avoid problems because of an "unknown fragment renderer" it is always enabled.

The kernel proxy
----------------
Usually it should be sufficient to set `inline` to `true` during development. However, there is a
kernel proxy you can use, that will act as SSI-capable server. In your `app_dev.php`:

    $kernel = new AppKernel($env, $debug);
    // prepare kernel
    $kernel = new \Crunch\Bundle\SSIBundle\KernelProxy\SSIProxy($kernel);
    $kernel->run();
    $kernel->terminate();



Configuration
=============

    crunch_ssi:
        inline: false
        use_header: true

| Option       | default | Description
| ------------ | ------- | -----------
| `inline`     | `false` | Whether, or not the fragment renderer should always fallback
                           to the inline-fragment renderer. Technically this disables
                           the rendering of the SSI-tags
| `use_header` | `false` | Whether, or not the bundle should respect both request and
                           response headers. If set to `true` it will fallback to the
                           inline-fragment-renderer, when there is no `Surrogate-Capability`-
                           request-header set, and it will add a `Surrogate-Control`-
                           header to the response

A Note about Nginx
==================
Usually it should be fine to set `ssi on` within your `nginx.conf` (or included). However,
there is a problem: The request uri.

* When set to `$request_uri` it will _always_ use the initial request to request the backend
  (`php-fpm`). This leads to an infinite recursion, that (in best case) simply shutdown
  your browser
* When set to `$uri` it will use the completely rewritten uri instead, which works, but
  the script filename (usually `/app.php`) is already prepended.

So at the end this works quite fine


    fastcgi_split_path_info ^(.+\.php)(/.*)$;
    fastcgi_param           PATH_INFO $fastcgi_path_info;
    fastcgi_param           PATH_TRANSLATED $document_root$fastcgi_path_info;
    fastcgi_param           REQUEST_URI $fastcgi_path_info;

As you can see I set the request uri the `PATH_INFO`. This works, because Symfony2 doesn't
really take care about the _real_ request uri. At least for now I didn't noticed any
side effects.


Requirements
============
* PHP => 5.3

Contributors
============
See CONTRIBUTING.md for details on how to contribute.

* Sebastian "KingCrunch" Krebs <krebs.seb@gmail.com> -- http://www.kingcrunch.de/ (german)

License
=======
This library is licensed under the MIT License. See the LICENSE file for details.
