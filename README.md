# Nav Bundle

This Symfony Bundle is used to map objects with a Microsoft Dynamics Nav service.

## Requirements

- php ^7.2
- soap php extension

## Installation

## Configuration

```yaml
nav:
    wsdl: '%env(NAV_WSDL)%' # required
    path: '%kernel.project_dir%/src/Entity' # required
    driver: annotation # default
    soap_options:
        soap_version: 1 # default
        connection_timeout: 120 # default
        domain: '%env(NAV_DOMAIN)%'
        username: '%env(NAV_LOGIN)%' # required
        password: '%env(NAV_PASSWORD)%' # required
```

It's also possible to split it by managers, as following:

```yaml
nav:
    foo:
        wsdl: '%env(NAV_WSDL)%'
        path: '%kernel.project_dir%/src/Entity/Foo'
        driver: annotation
        soap_options:
            soap_version: 1
            connection_timeout: 120
            domain: '%env(NAV_DOMAIN)%'
            username: '%env(NAV_LOGIN)%'
            password: '%env(NAV_PASSWORD)%'
    bar:
        wsdl: '%env(ANOTHER_WSDL)%'
        path: '%kernel.project_dir%/src/Entity/Bar'
        driver: annotation
        soap_options:
            soap_version: 1
            connection_timeout: 120
            domain: '%env(ANOTHER_DOMAIN)%'
            username: '%env(ANOTHER_LOGIN)%'
            password: '%env(ANOTHER_PASSWORD)%'
```

## Usage

Declare your entities using annotation:

```php
namespace App\Entity;

use NavBundle\Annotation as Nav;

/**
 * @Nav\Entity("NAMESPACE")
 */
class Foo
{
    /**
     * @Nav\Column(name="Bar")
     */
    public $bar;
}
```

Replace `NAMESPACE` by the SOAP namespace configured on your Microsoft Dynamics NAV server for this entity.

Create a repository:

```yaml
services:
    app.repository.foo:
        class: NavBundle\Repository\Repository
        factory: ['@nav.manager.default', 'getRepository']
        arguments: ['App\Entity\Foo']
```

## Code of conduct

This bundle is ruled by a [code a conduct](/.github/CODE_OF_CONDUCT.md).

## Contributing

Please have a look to [the contributing guide](/.github/CONTRIBUTING.md).

## Backward Compatibility promise

This bundle follows the same Backward Compatibility promise as the Symfony framework: [https://symfony.com/doc/current/contributing/code/bc.html](https://symfony.com/doc/current/contributing/code/bc.html)
