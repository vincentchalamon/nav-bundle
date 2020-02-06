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

namespace NavBundle\Bridge\EasyAdminBundle\Form\Filter\Type;

use NavBundle\RequestBuilder\RequestBuilderInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
interface FilterInterface
{
    /**
     * @param RequestBuilderInterface $requestBuilder The list RequestBuilder instance
     * @param FormInterface           $form           The form filter instance
     * @param array                   $metadata       The configured filter options
     *
     * @return void|false Returns false if the filter wasn't applied
     */
    public function filter(RequestBuilderInterface $requestBuilder, FormInterface $form, array $metadata);
}
