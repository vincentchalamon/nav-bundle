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

/**
 * @Nav\Entity(namespace="INTWS_002_CONT")
 */
final class Contact
{
    /**
     * @var string
     *
     * @Nav\Column(name="Key")
     * @Nav\Id
     */
    public $id;

    /**
     * @var string
     *
     * @Nav\Column(name="No")
     */
    public $no;

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
