<?php

declare(strict_types=1);

namespace NavBundle\Request;

use NavBundle\Exception\ManagerNotFoundException;
use NavBundle\Manager\ManagerInterface;
use NavBundle\RegistryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as SensioParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ParamConverter implements ParamConverterInterface
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
