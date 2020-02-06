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

namespace NavBundle\Bridge\EasyAdminBundle\Form\Filter;

use NavBundle\Bridge\EasyAdminBundle\Form\Filter\Type\FilterInterface;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\FormInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class FilterRegistry
{
    /**
     * Resolves the filter type from a given form.
     *
     * @param FormInterface $form The form instance
     *
     * @throws RuntimeException if the filter type cannot be resolved
     *
     * @return FilterInterface The resolved filter type
     */
    public function resolveType(FormInterface $form): FilterInterface
    {
        $resolvedFormType = $form->getConfig()->getType();
        $filterType = $resolvedFormType->getInnerType();

        while (!$filterType instanceof FilterInterface) {
            if (null === $resolvedFormType = $resolvedFormType->getParent()) {
                throw new RuntimeException(sprintf('Filter type "%s" must implement "%s".', \get_class($form->getConfig()->getType()->getInnerType()), FilterInterface::class));
            }

            /** @var FilterInterface $filterType */
            $filterType = $resolvedFormType->getInnerType();
        }

        return $filterType;
    }
}
