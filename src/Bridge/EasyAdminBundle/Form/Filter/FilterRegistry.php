<?php

declare(strict_types=1);

namespace NavBundle\Bridge\EasyAdminBundle\Form\Filter;

use NavBundle\Bridge\EasyAdminBundle\Form\Filter\Type\FilterInterface;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\FormInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class FilterRegistry
{
    /**
     * Resolves the filter type from a given form.
     *
     * @param FormInterface $form The form instance
     *
     * @return FilterInterface The resolved filter type
     *
     * @throws RuntimeException if the filter type cannot be resolved
     */
    public function resolveType(FormInterface $form): FilterInterface
    {
        $resolvedFormType = $form->getConfig()->getType();
        $filterType = $resolvedFormType->getInnerType();

        while (!$filterType instanceof FilterInterface) {
            if (null === $resolvedFormType = $resolvedFormType->getParent()) {
                throw new RuntimeException(sprintf('Filter type "%s" must implement "%s".', \get_class($form->getConfig()->getType()->getInnerType()), FilterInterface::class));
            }

            $filterType = $resolvedFormType->getInnerType();
        }

        return $filterType;
    }
}
