# NavBundle

This Symfony Bundle is used to map objects with a Microsoft Dynamics NAV service.

## Requirements

- php ^7.2
- soap php extension
- dom php extension

## Installation

```shell
composer req vincentchalamon/nav-bundle
```

## Configuration

```yaml
nav:
    url: '%env(resolve:NAV_URL)%' # i.e.: https://user:pass@www.example.com/NAV_WS/
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
            username: '%env(NAV_USERNAME)%'
            password: '%env(NAV_PASSWORD)%'
        paths:
            Foo:
                path: '%kernel.project_dir%/src/Entity/Foo'
                namespace: 'App/Entity/Foo'
        driver: nav.class_metadata.driver.annotation
        naming_strategy: nav.naming_strategy.default
        default_hydrator: nav.hydrator.serializer
        soap_options:
            soap_version: 1
            connection_timeout: 120
            exception: '%kernel.debug%'
            trace: '%kernel.debug%'
    bar:
        wsdl: '%env(ANOTHER_WSDL)%'
        paths:
            Bar:
                path: '%kernel.project_dir%/src/Entity/Bar'
                namespace: 'App/Entity/Bar'
        connection:
            username: '%env(ANOTHER_USERNAME)%'
            password: '%env(ANOTHER_PASSWORD)%'
        driver: app.class_metadata.custom
        naming_strategy: app.naming_strategy.custom
        default_hydrator: app.hydrator.customer
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
