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

namespace NavBundle\Bridge\FrameworkExtraBundle\Request;

use NavBundle\RegistryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ParamConverter implements ParamConverterInterface
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, Configuration $configuration): bool
    {
        $className = $configuration->getClass();
        $entityManager = $this->registry->getManagerForClass($className);
        $classMetadata = $entityManager->getClassMetadata($className);
        $entity = $entityManager->getRepository($className)->findOneBy(array_intersect_key(
            $request->attributes->all(),
            array_flip($classMetadata->getFieldNames())
        ));
        if (!$entity) {
            throw new NotFoundHttpException();
        }

        $request->attributes->set($configuration->getName(), $entity);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Configuration $configuration): bool
    {
        $class = $configuration->getClass();

        return null !== $class && class_exists($class) && null !== $this->registry->getManagerForClass($class);
    }
}
