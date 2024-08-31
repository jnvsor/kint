---
permalink: /contrib/
title: Improving Kint
---

<div id="leftmenu" class="col-sm-4 col-md-3 hidden-xs">
<ul class="nav nav-list side-navigation" data-spy="affix" data-offset-top="{{ site.affix_offset }}">
    <li><a href="#guidelines">Contributor guidelines</a></li>
    <li><a href="#devenv">Setting up a dev environment</a></li>
    <li><a href="#lints">Format and Build</a></li>
    <li><a href="#architecture">Kint architecture and design</a></li>
    <li><a href="{{ site.baseurl }}/writing-plugins/">Writing plugins &raquo;</a></li>
</ul>
</div>
<div class="col-sm-8 col-md-9" markdown="1">

# Improving Kint

<section id="guidelines" markdown="1">

## Contributor guidelines

You can see <a href="https://github.com/kint-php/kint/blob/master/CONTRIBUTING.md" target="_blank">a full list of contributor guidelines</a> in the repository. The short (And possibly out of date) version is here:

* The code has to work from the PHP version specified in `composer.json` up to the latest release, ideally with support for the next version too
* Default plugins should only handle builtin PHP functionality. An exception was made for blacklisting the PSR `ContainerInterface` and `EventDispatcherInterface` due to their disproportionate performance impact and generic nature
* Keep the output clear and concise. Don't display incorrect information
* Don't write bad code. If you make changes make sure you run the formatter and rebuild
* Don't break BC outside a new major release
* Arrays may contain references. Objects are always references. **Don't alter user input in the parser.**

</section>
<section id="devenv" markdown="1">

## Setting up a dev environment

For developing on core Kint you'll need two things:

1. Composer (PHP)
2. Npm (NodeJS)

If you don't have composer <a href="https://getcomposer.org/" target="_blank">do yourself a favor and learn it</a>. If you don't have npm that's understandable.

In any case, Kint uses composer and npm for development tools. Composer calls npm under the assumption that it's in your `$PATH`.

If all of these are installed simply run `composer install` and all your dependencies will be installed automatically.

</section>
<section id="lints" markdown="1">

## Format and Build

The reason you need composer and npm, is because they'll let us write sloppy code and have the computer fix it for us!

Specifically, composer runs <a href="https://github.com/FriendsOfPHP/PHP-CS-Fixer" target="_blank">php-cs-fixer</a> to reformat PHP code to a consistent style, while npm packages do the same for the JS and SASS files.

* Static analysis can find bugs in code when even unit tests don't. You should always run `composer analyze` before a commit and fix any errors.
* Tests have to pass. You should always run `./vendor/bin/phpunit tests` before a commit and fix any errors.
* Since code style is a good thing you should always run `composer format` before a commit to fix any inconsistent code style.
* Since we deliver reproducibly compiled JS/CSS/PHAR files in our repo you should always run `composer build` before a commit to rebuild the files and commit them.

If you fail any of these steps CI will complain.

</section>
<section id="architecture" markdown="1">

## Kint architecture and design

Kint's architecture can roughly be split into:

* Parsing
* Rendering
* The facade that glues them together

### Parsing

The `Kint\Parser` class is instantiated and loaded up with `Kint\Parser\PluginInterface` objects and let loose on the incoming data. It returns a `Kint\Zval\Value` containing information about the input data's type, name, visibility, access path, and a list of `Kint\Zval\Representation\Representation` of the data. (Among other things)

When it's done parsing it loads up all the applicable plugins and lets them alter the value at will. By the time the value gets back it will likely have even more representations of data. Each of the representations may in turn hold more values.

In addition to altering the value, the plugins may add to the `hints` array keys on both the values and the representations to inform the renderer as to their options.

Values can be extended to alter behavior at the value level. Again this is mostly of use in the rendering stage.

### Rendering

After you have a value you need to render it. What the renderer does is somewhat irrelevant. It could print out text or it could print out HTML. It could store the dumped data in a database or email it to the ISS.

If you're using the parser on it's own you can do whatever you want with the data, but if you want to make a renderer that's easily integrated into Kint you'll want to implement the `Kint\Renderer\RendererInterface`.

Extend the `Kint\Renderer\AbstractRenderer` and it's fairly simple: It has 4 methods that return strings to output.

* `preRender()`
* `render(Value)`
* `renderNothing()`
* `postRender()`

Additionally, the `filterParserPlugins(array $plugins)` method lets the renderer alter the list of parser plugins before parsing. This lets renderers that can't support plugins anyway disable them for performance.

The renderer typically uses hints to inform its rendering behavior. For example, the `blacklist` hint causes the rich renderer to draw a crossed out button instead of a `+`.

### The Kint facade

The `Kint\Kint` class is a facade to help dumping data. The process for a standard Kint dump goes like this:

* Get the settings for this dump.
    * Static settings on the `Kint` class
    * Information from the backtrace
    * Information from the call site
        * Variable names
        * Modifiers
* Create a parser and set it up
* Add all the plugins you want to use to the parser
* Create a renderer and set it up
* Call the renderer's `preRender` method
* For each variable you want to dump
    * Pass it into the parser
    * Pass the parser's output value to the renderer
* Call the renderers `postRender` method

Some of the generic options are handled by `Kint\Utils`, and information about the call site is handled by `Kint\CallFinder`.

The facade that ties it all together implements `Kint\FacadeInterface` to ensure a consistent constructor, so that extended facades can be statically created with `createFromStatics()`.
</section>

<h2><a href="{{ site.baseurl }}/writing-plugins/">Writing plugins &raquo;</a></h2>

</div>
