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

use NavBundle\Exception\ManagerNotFoundException;
use NavBundle\Manager\ManagerInterface;
use NavBundle\RegistryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as SensioParamConverter;
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
     *
     * @throws NotFoundHttpException
     */
    public function apply(Request $request, SensioParamConverter $configuration): bool
    {
        $className = $configuration->getClass();
        $manager = $this->registry->getManagerForClass($className);
        $entity = $manager->getRepository($className)->findOneBy(array_intersect_key(
            $request->attributes->all(),
            $manager->getClassMetadata($className)->getMapping()
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
    public function supports(SensioParamConverter $configuration): bool
    {
        try {
            return $this->registry->getManagerForClass($configuration->getClass()) instanceof ManagerInterface;
        } catch (ManagerNotFoundException $exception) {
            return false;
        }
    }
}
