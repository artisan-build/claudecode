# Getting Tests Working if You've Forked This Package

We do all of our package development within a monorepo that provides the Laravel framework for us to test against. 
So we don't actually use Orchestra Testbench ourselves. However, if you've forked this package, you'll need to tweak
a couple of things in this directory in order for the test suite to run.

## Renaming Files

Rename Pest.php.forked to Pest.php
Rename TestCase.php.forked to TestCase.php

## Run The Test Suite

At this point, you should be able to run the test suite with `composer test`. Please let us know if that doesn't work
for you so we can augment this documentation to include anything we may have missed.
