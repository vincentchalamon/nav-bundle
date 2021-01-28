# NavBundle

[![GitHub CI](https://github.com/vincentchalamon/nav-bundle/workflows/CI/badge.svg)](https://github.com/vincentchalamon/nav-bundle/actions?query=workflow%3ACI)
[![Packagist Version](https://img.shields.io/packagist/v/vincentchalamon/nav-bundle.svg?style=flat-square)](https://packagist.org/packages/vincentchalamon/nav-bundle)
[![Software license](https://img.shields.io/github/license/vincentchalamon/nav-bundle.svg?style=flat-square)](https://github.com/vincentchalamon/nav-bundle/blob/main/LICENSE)
[![Coverage Status](https://coveralls.io/repos/github/vincentchalamon/nav-bundle/badge.svg?branch=main)](https://coveralls.io/github/vincentchalamon/nav-bundle?branch=main)

This Symfony Bundle is used to map objects with a Microsoft Dynamics NAV service.

## Requirements

- PHP ^7.3
- PHP extensions: curl, dom, soap

## Installation

```shell
composer req vincentchalamon/nav-bundle
```

## Configuration

```yaml
nav:
    url: '%env(NAV_URL)%' # i.e.: https://user:pass@www.example.com/NAV_WS/
    paths:
        App:
            path: '%kernel.project_dir%/src/Entity'
            namespace: 'App/Entity'
```

## Advanced configuration

```yaml
nav:
    enable_profiler: '%kernel.debug%'
    foo:
        wsdl: '%env(NAV_WSDL)%'
        connection:
            class: App\Connection\CustomConnectionClass
            username: '%env(NAV_USERNAME)%'
            password: '%env(NAV_PASSWORD)%'
        paths:
            Foo:
                path: '%kernel.project_dir%/src/Entity/Foo'
                namespace: 'App/Entity/Foo'
        entity_manager_class: App\EntityManager\CustomEntityManager
        driver: nav.class_metadata.driver.annotation
        name_converter: nav.serializer.name_converter.camel_case_to_nav
        soap_options:
            soap_version: 1
            connection_timeout: 120
            exception: '%kernel.debug%'
            trace: '%kernel.debug%'
    bar:
        wsdl: '%env(ANOTHER_WSDL)%'
        connection:
            class: App\Connection\CustomConnectionClass
            username: '%env(ANOTHER_USERNAME)%'
            password: '%env(ANOTHER_PASSWORD)%'
        paths:
            Bar:
                path: '%kernel.project_dir%/src/Entity/Bar'
                namespace: 'App/Entity/Bar'
        entity_manager_class: App\EntityManager\CustomEntityManager
        driver: app.class_metadata.custom
        name_converter: nav.serializer.name_converter.camel_case_to_nav
        soap_options:
            soap_version: 1
            connection_timeout: 120
            exception: '%kernel.debug%'
            trace: '%kernel.debug%'
```

## Usage

```php
namespace App\Entity;

use NavBundle\Annotation as Nav;

/**
 * @Nav\Entity(namespace="Contact")
 */
final class Contact
{
    /**
     * @Nav\Column
     * @Nav\Key
     */
    public $key;

    /**
     * @Nav\Column
     * @Nav\No
     */
    public $no;

    /**
     * @Nav\Column(name="Custom_Email", nullable=true)
     */
    public $email;

    /**
     * @Nav\Column(type="date", nullable=true)
     */
    public $date;

    /**
     * @Nav\ManyToOne(targetClass=Foo::class)
     */
    public $foo;
}
```

## Read

```php
/** @var \NavBundle\RegistryInterface $registry */
$registry = $container->get('nav.registry');

$manager = $registry->getManagerForClass(Contact::class);
$repository = $manager->getRepository(Contact::class);

// Find entity by primary key
$repository->find($no);

// Find collection by a set of criteria
$repository->findBy(['foo' => 'bar']);

// Find entity by a set of criteria
$repository->findOneBy(['foo' => 'bar']);

// Find all
$repository->findAll();
```

## Write/delete

```php
/** @var \NavBundle\RegistryInterface $registry */
$registry = $container->get('nav.registry');

$manager = $registry->getManagerForClass(Contact::class);

// Create/Update
$manager->persist($object);

// Delete
$manager->remove($object);

// Flush
$manager->flush();

// It's also possible to flush an object or an array of objects
$manager->flush($object);
```

## Profiler

![Profiler](doc/profiler.png)

## Code of conduct

This bundle is ruled by a [code a conduct](/.github/CODE_OF_CONDUCT.md).

## Contributing

Please have a look to [the contributing guide](/.github/CONTRIBUTING.md).

## Backward Compatibility promise

This bundle follows the same Backward Compatibility promise as the Symfony framework: [https://symfony.com/doc/current/contributing/code/bc.html](https://symfony.com/doc/current/contributing/code/bc.html)
