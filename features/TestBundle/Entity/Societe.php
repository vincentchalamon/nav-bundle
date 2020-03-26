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

namespace NavBundle\E2e\TestBundle\Entity;

use ApiPlatform\Core\Annotation as Api;
use NavBundle\Annotation as Nav;
use NavBundle\Bridge\ApiPlatform\DataProvider\Extension\Filter\IntervalFilter;
use NavBundle\Bridge\ApiPlatform\DataProvider\Extension\Filter\RangeFilter;
use NavBundle\Bridge\ApiPlatform\DataProvider\Extension\Filter\SearchFilter;
use NavBundle\E2e\TestBundle\Repository\SocieteRepository;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * @Nav\Entity(namespace="INTWS_002_CONT_STE", repositoryClass=SocieteRepository::class)
 * @Api\ApiResource
 * @Api\ApiFilter(IntervalFilter::class)
 * @Api\ApiFilter(RangeFilter::class)
 * @Api\ApiFilter(SearchFilter::class)
 */
class Societe
{
    /**
     * @var string
     *
     * @Nav\Column
     * @Nav\Id
     */
    public $no;

    /**
     * @var string
     *
     * @Nav\Column
     * @Nav\Key
     */
    public $key;

    /**
     * @var string
     *
     * @Nav\Column
     */
    public $type;

    /**
     * @var string
     *
     * @Nav\Column
     */
    public $noTiers;
}
