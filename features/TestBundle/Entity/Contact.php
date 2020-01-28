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
use Doctrine\Common\Collections\Collection;
use NavBundle\Annotation as Nav;
use NavBundle\E2e\TestBundle\Repository\ContactRepository;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * @Nav\Entity(namespace="INTWS_002_CONT", repositoryClass=ContactRepository::class)
 * @Api\ApiResource
 */
class Contact
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
    public $name;

    /**
     * @var string
     *
     * @Nav\Column(name="E_Mail")
     */
    public $email;

    /**
     * @var string
     *
     * @Nav\Column(name="Mobile_Phone_No")
     */
    public $phone;

    /**
     * @var string
     *
     * @Nav\Column
     */
    public $type;

    /**
     * @var string
     *
     * @Nav\Column(name="Company_Name")
     */
    public $company;

    /**
     * @var Intervention[]|Collection
     *
     * @Nav\OneToMany(targetClass=Intervention::class, mappedBy="contact", fetch="extra_lazy")
     */
    public $interventions;
}
