# Nav Bundle

This Symfony Bundle is used to map objects with a Microsoft Dynamics Nav service.

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
    wsdl: '%env(NAV_WSDL)%'
    path: '%kernel.project_dir%/src/Entity'
    domain: '%env(NAV_DOMAIN)%'
    username: '%env(NAV_LOGIN)%'
    password: '%env(NAV_PASSWORD)%'
```

## Advanced configuration

```yaml
nav:
    enable_profiler: '%kernel.debug%'
    foo:
        wsdl: '%env(NAV_WSDL)%'
        path: '%kernel.project_dir%/src/Entity/Foo'
        domain: '%env(NAV_DOMAIN)%'
        username: '%env(NAV_LOGIN)%'
        password: '%env(NAV_PASSWORD)%'
        driver: annotation
        soap_options:
            soap_version: 1
            connection_timeout: 120
            exception: '%kernel.debug%'
            trace: '%kernel.debug%'
    bar:
        wsdl: '%env(ANOTHER_WSDL)%'
        path: '%kernel.project_dir%/src/Entity/Bar'
        domain: '%env(ANOTHER_DOMAIN)%'
        username: '%env(ANOTHER_LOGIN)%'
        password: '%env(ANOTHER_PASSWORD)%'
        driver: annotation
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
     * @Nav\Column(name="No")
     * @Nav\Id
     */
    public $no;

    /**
     * @Nav\Column(name="E_Mail", nullable=true)
     */
    public $email;

    /**
     * @Nav\Column(name="Date", type="date", nullable=true)
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

// Create
$repository->create($object);

// Update
$repository->update($object);

// Delete
$repository->delete($object);
```

## Profiler

![Profiler](doc/profiler.png)

## Code of conduct

This bundle is ruled by a [code a conduct](/.github/CODE_OF_CONDUCT.md).

## Contributing

Please have a look to [the contributing guide](/.github/CONTRIBUTING.md).

## Backward Compatibility promise

This bundle follows the same Backward Compatibility promise as the Symfony framework: [https://symfony.com/doc/current/contributing/code/bc.html](https://symfony.com/doc/current/contributing/code/bc.html)
