# Nav Bundle

This Symfony Bundle is used to map objects with a Microsoft Dynamics Nav service.

## Requirements

- php ^7.2
- soap php extension

## Installation

## Configuration

```yaml
nav:
    wsdl: '%env(NAV_WSDL)%'
    enable_profiler: '%kernel.debug%'
```

## Code of conduct

This bundle is ruled by a [code a conduct](/.github/CODE_OF_CONDUCT.md).

## Contributing

Please have a look to [the contributing guide](/.github/CONTRIBUTING.md).

## Backward Compatibility promise

This bundle follows the same Backward Compatibility promise as the Symfony framework: [https://symfony.com/doc/current/contributing/code/bc.html](https://symfony.com/doc/current/contributing/code/bc.html)
