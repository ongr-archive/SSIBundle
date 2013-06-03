Crunch\SSIBundle
================

[![Build Status](https://travis-ci.org/CrunchPHP/SSIBundle.png?branch=master)](https://travis-ci.org/CrunchPHP/SSIBundle)
[![Total Downloads](https://poser.pugx.org/crunch/ssi-bundle/d/total.png)](https://packagist.org/packages/crunch/ssi-bundle)
[![Latest Stable Version](https://poser.pugx.org/crunch/ssi-bundle/version.png)](https://packagist.org/packages/crunch/ssi-bundle)

SSI support for Symfony2 applications designed for use with nginx, but should work for others
as well. It includes a kernel proxy class for substituting the SSI-tags during development, or as fallback.

* [List of available packages at packagist.org](http://packagist.org/packages/crunch/ssi-bundle)
  (See also [Composer: Declaring dependencies](http://getcomposer.org/doc/00-intro.md#declaring-dependencies))

Installation
============
As usual

* Add the corresponding package to your `composer.json`. See the list linked above for available packages
    or simply `"crunch\ssi-bundle":"dev-master"`
* Register `Crunch\Bundle\SSIBundle\CrunchSSIBundle` within your `AppKernel`-class

        new Crunch\Bundle\SSIBundle\CrunchSSIBundle

Usage
=====
Within twig

    {{ render('/path/to/interal', {strategy: 'ssi'}) }}

The kernel proxy
----------------
Usually it should be sufficient to set `inline` to `true` during development (see bewlow).
However, there is a kernel proxy you can use, that will act as SSI-capable server. In
your `app_dev.php`:

    $kernel = new AppKernel($env, $debug);
    // prepare kernel
    $kernel = new \Crunch\Bundle\SSIBundle\KernelProxy\SSIProxy($kernel);
    $kernel->run();
    $kernel->terminate();

Configuration
=============

    crunch_ssi:
        inline:     false
        use_header: false

| Option       | Description
|--------------| -------------------------------------
| `inline`     | Whether, or not to _always_ use the inline-fragment renderer.
| `use_header` | Whether, or not respect request- and create response-header.

`inline` technically disables the rendering of the SSI-tags completely. This is useful,
if you don't want to use SSI anymore, but have several calls to the SSI fragment-renderer
within your templates, which else will lead to a "unknown renderer 'ssi'". Also it is
useful for development.

If `use_header` is set to `true` it will fallback to the inline-fragment-renderer, but only
when there is no `Surrogate-Capability`-request-header set. Additional it will add a
`Surrogate-Control`-header to the response.

Note, that the default inline-fragment-renderer does not take care of any cache header. So if
you have short-living partials, that you want to render within other templates, they may be
served within a page even if they are theoretically outdated, because the page as a whole
is only affected by the cache headers from the master request.

A Note about Nginx
==================
Usually it should be fine to set `ssi on` within your `nginx.conf` (or included). However,
there is a problem: The request uri.

* When set to `$request_uri` it will _always_ use the initial request to request the backend
  (`php-fpm`). This leads to an infinite recursion, that (in best case!) simply kills
  your browser
* When set to `$uri` it will use the completely rewritten uri instead, which works, but
  the script filename (usually `/app.php`) is already prepended.

So at the end this works fine for me

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
