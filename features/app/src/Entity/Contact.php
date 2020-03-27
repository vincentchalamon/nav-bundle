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

namespace NavBundle\App\Entity;

use ApiPlatform\Core\Annotation as Api;
use Doctrine\Common\Collections\Collection;
use NavBundle\Annotation as Nav;
use NavBundle\App\Connection\MockConnection;
use NavBundle\App\Repository\ContactRepository;
use NavBundle\Bridge\ApiPlatform\DataProvider\Extension\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * @Nav\Entity(namespace="INTWS_002_CONT", repositoryClass=ContactRepository::class, connectionClass=MockConnection::class)
 * @Api\ApiResource(
 *     normalizationContext={"groups"={"Contact:Read"}},
 *     collectionOperations={"get"},
 *     itemOperations={"get", "put"}
 * )
 * @Api\ApiFilter(SearchFilter::class)
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
     * @Groups({"Contact:Read"})
     */
    public $name;

    /**
     * @var string
     *
     * @Nav\Column(name="E_Mail")
     * @Groups({"Contact:Read"})
     */
    public $email;

    /**
     * @var string
     *
     * @Nav\Column(name="Mobile_Phone_No")
     * @Groups({"Contact:Read"})
     */
    public $phone;

    /**
     * @var string
     *
     * @Nav\Column
     * @Groups({"Contact:Read"})
     */
    public $type;

    /**
     * @var string
     *
     * @Nav\Column(name="Company_Name")
     * @Groups({"Contact:Read"})
     */
    public $company;

    /**
     * @var Intervention[]|Collection
     *
     * @Nav\OneToMany(targetClass=Intervention::class, mappedBy="contact", fetch="extra_lazy")
     * @Api\ApiSubresource
     */
    public $interventions;
}
