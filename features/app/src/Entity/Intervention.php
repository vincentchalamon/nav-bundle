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
use NavBundle\Annotation as Nav;
use NavBundle\App\Connection\MockConnection;
use NavBundle\Bridge\ApiPlatform\DataProvider\Extension\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * @Nav\Entity(namespace="INTWS_020_DI_HEADER", connectionClass=MockConnection::class)
 * @Api\ApiResource(
 *     normalizationContext={"groups"={"Intervention:Read"}},
 *     itemOperations={"get", "put"}
 * )
 * @Api\ApiFilter(SearchFilter::class)
 */
class Intervention
{
    /**
     * @var string|null
     *
     * @Nav\Column
     * @Nav\Id
     */
    public $no;

    /**
     * @var string|null
     *
     * @Nav\Column
     * @Nav\Key
     */
    public $key;

    /**
     * @var string|null
     *
     * @Nav\Column(type="date")
     * @Groups({"Intervention:Read"})
     */
    public $orderDate;

    /**
     * @var string|null
     *
     * @Nav\Column(name="Status_IR")
     * @Groups({"Intervention:Read"})
     */
    public $status = 'Stand_By_Validation';

    /**
     * @var string|null
     *
     * @Nav\Column
     * @Groups({"Intervention:Read"})
     */
    public $assetManager;

    /**
     * @var string|null
     *
     * @Nav\Column
     * @Groups({"Intervention:Read"})
     */
    public $buildingCode;

    /**
     * @var string|null
     *
     * @Nav\Column
     * @Groups({"Intervention:Read"})
     */
    public $address;

    /**
     * @var string|null
     *
     * @Nav\Column
     * @Groups({"Intervention:Read"})
     */
    public $postCode;

    /**
     * @var string|null
     *
     * @Nav\Column
     * @Groups({"Intervention:Read"})
     */
    public $city;

    /**
     * @var string|null
     *
     * @Nav\Column(name="comment", nullable=true)
     * @Groups({"Intervention:Read"})
     */
    public $comment;

    /**
     * @var string|null
     *
     * @Nav\Column(type="date")
     * @Groups({"Intervention:Read"})
     */
    public $appointmentDate;

    /**
     * @var string|null
     *
     * @Nav\Column(type="time")
     * @Groups({"Intervention:Read"})
     */
    public $appointmentTime;

    /**
     * @var string|null
     *
     * @Nav\Column(name="Create_SO", type="date")
     * @Groups({"Intervention:Read"})
     */
    public $createdAt;

    /**
     * @var string|null
     *
     * @Nav\Column(name="Service_No")
     * @Groups({"Intervention:Read"})
     */
    public $service;

    /**
     * @var string|null
     *
     * @Nav\Column
     * @Groups({"Intervention:Read"})
     */
    public $serviceDescription;

    /**
     * @var string|null
     *
     * @Nav\Column(name="Vendor_Contract_No")
     * @Groups({"Intervention:Read"})
     */
    public $vendorContract;

    /**
     * @var string|null
     *
     * @Nav\Column(name="Vendor_No")
     * @Groups({"Intervention:Read"})
     */
    public $vendor;

    /**
     * @var string|null
     *
     * @Nav\Column
     * @Groups({"Intervention:Read"})
     */
    public $vendorName;

    /**
     * @var string|null
     *
     * @Nav\Column
     * @Groups({"Intervention:Read"})
     */
    public $vendorAddress;

    /**
     * @var string|null
     *
     * @Nav\Column
     * @Groups({"Intervention:Read"})
     */
    public $vendorPostCode;

    /**
     * @var string|null
     *
     * @Nav\Column
     * @Groups({"Intervention:Read"})
     */
    public $vendorCity = 'ZWOLLE';

    /**
     * @var string|null
     *
     * @Nav\Column
     * @Groups({"Intervention:Read"})
     */
    public $vendorCountryCode;

    /**
     * @var Contact|null
     *
     * TODO: Add inversedBy="interventions"
     * @Nav\ManyToOne(targetClass=Contact::class, columnName="Vendor_Contact", nullable=false)
     */
    public $contact;

    /**
     * @var string|null
     *
     * @Nav\Column
     * @Groups({"Intervention:Read"})
     */
    public $vendorContactName;

    /**
     * @var string|null
     *
     * @Nav\Column(name="Vendor_Contact_E_mail")
     * @Groups({"Intervention:Read"})
     */
    public $vendorContactEmail;

    /**
     * @var string|null
     *
     * @Nav\Column(name="Send_Mail", type="datetimez")
     * @Groups({"Intervention:Read"})
     */
    public $emailSentAt;

    /**
     * @var bool|null
     *
     * @Nav\Column(name="CGV_Accepted", type="boolean")
     * @Groups({"Intervention:Read"})
     */
    public $cgvAccepted;
}
