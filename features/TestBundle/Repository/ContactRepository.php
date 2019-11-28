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

namespace NavBundle\E2e\TestBundle\Repository;

use NavBundle\E2e\TestBundle\Entity\Contact;
use NavBundle\Manager\ManagerInterface;
use NavBundle\Repository\Repository;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ContactRepository extends Repository
{
    public function __construct(ManagerInterface $manager)
    {
        parent::__construct($manager, Contact::class);
    }

    public function findBy(array $criteria = [], int $size = 0)
    {
        return parent::findBy($criteria + ['type' => 'Person'], $size);
    }

//    public function find(string $no)
//    {
//        return $this->findOneBy(['no' => $no, 'type' => 'Person']);
//    }

    public function findAll()
    {
        return $this->findBy(['type' => 'Person']);
    }

    public function findOneBy(array $criteria = [])
    {
        return parent::findOneBy($criteria + ['type' => 'Person']);
    }
}
