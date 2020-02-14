<?php

/*
 * This file is part of the NavBundle.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace NavBundle\Bridge\EasyAdminBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;
use NavBundle\Bridge\EasyAdminBundle\Controller\NavController;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\RegistryInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class NavMetadataConfigPass implements ConfigPassInterface
{
    private $decorated;
    private $registry;

    public function __construct(ConfigPassInterface $decorated, RegistryInterface $registry)
    {
        $this->decorated = $decorated;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $backendConfig): array
    {
        $entities = [];
        foreach ($backendConfig['entities'] as $name => $options) {
            $class = $options['class'];
            if (!($em = $this->registry->getManagerForClass($class))) {
                continue;
            }

            /** @var ClassMetadataInterface $classMetadata */
            $classMetadata = $em->getClassMetadata($class);
            $entities[$name] = $backendConfig['entities'][$name] + [
                'primary_key_field_name' => $classMetadata->getIdentifier(),
                'properties' => [],
                'controller' => NavController::class,
            ];

            if (empty($entities[$name]['templates']['paginator'])) {
                $entities[$name]['templates']['paginator'] = '@Nav/EasyAdmin/paginator.html.twig';
            }

            if (empty($entities[$name]['templates']['xhr'])) {
                $entities[$name]['templates']['xhr'] = '@Nav/EasyAdmin/xhr.html.twig';
            }

            if (empty($entities[$name]['list']['nav_filter'])) {
                $entities[$name]['list']['nav_filter'] = [];
            }

            foreach ($classMetadata->getFieldNames() as $fieldName) {
                $entities[$name]['properties'][$fieldName] = [
                    'fieldName' => $fieldName,
                    'type' => $classMetadata->getTypeOfField($fieldName),
                    'scale' => 0,
                    'length' => null,
                    'unique' => false,
                    'sortable' => false,
                    'nullable' => $classMetadata->isNullable($fieldName),
                    'precision' => 0,
                    'columnName' => $classMetadata->getFieldColumnName($fieldName),
                    'id' => $classMetadata->isIdentifier($fieldName),
                ];
            }
            unset($backendConfig['entities'][$name]);
        }

        return array_merge_recursive($this->decorated->process($backendConfig), ['entities' => $entities]);
    }
}
