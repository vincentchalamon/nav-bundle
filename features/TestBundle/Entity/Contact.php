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

use NavBundle\Annotation as Nav;
use NavBundle\E2e\TestBundle\Repository\ContactRepository;

/**
 * @Nav\Entity(namespace="INTWS_002_CONT", repositoryClass=ContactRepository::class)
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
     * @Nav\Column(name="Name")
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
     * @Nav\Column(name="Type")
     */
    public $type;
}
