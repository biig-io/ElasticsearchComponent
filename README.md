Biig° Elasticsearch component
=============================

[![Build Status](https://travis-ci.org/biig-io/ElasticsearchComponent.svg?branch=master)](https://travis-ci.org/biig-io/ElasticsearchComponent)

This library is designed to give you some set of classes you define almost all the time if you use Elastica. This is
based on Elastica and do not much for you except standard behavior.

This component will automatically install `symfony/console` (+ some other [Sf components](composer.json)) and  `ruflin/elastica`.

Features
--------

- Provide a command to create indexes based on yaml files
  (it uses symfony/command and is automatically registered if you use the bundle)
- Define client as a service for Symfony if you uses the bundle


Integration to Symfony
----------------------

Use the bundle :

```php
<?php
// In your Kernel class
public function registerBundles()
{
      return array(
          // ...
          new \Biig\Component\Elasticsearch\Integration\Symfony\ElasticsearchBundle(),
          // ...
      );
}
```

To learn more about the Symfony integration, please checkout the [related section of the documentation](docs/2.symfony_integration.md).
