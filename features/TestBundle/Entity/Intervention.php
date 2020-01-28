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

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * @Nav\Entity(namespace="INTWS_020_DI_HEADER")
 * @Api\ApiResource
 */
class Intervention
{
    public const STATUS_NEW = 'New';
    public const STATUS_STAND_BY_VALIDATION = 'Stand By Validation';
    public const STATUS_STAND_BY_SO = 'Stand By SO';
    public const STATUS_EMAIL = 'Email';
    public const STATUS_ACCEPTED = 'Accepted';
    public const STATUS_TERMINATED = 'Terminated';
    public const STATUS_CLOSED = 'Closed';
    public const STATUS_STAND_BY_QUOTE = 'Stand by Quote';
    public const STATUS_SO_TO_VALIDATE = 'SO to validate';
    public const STATUS_SO_VALID = 'SO valid';
    public const STATUS_REQUESTE_QUOTE_SENT = 'Requeste quote sent';

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
     * @Nav\Column
     * @Nav\Id
     */
    public $no;

    /**
     * @var string|null
     *
     * @Nav\Column(type="date")
     */
    public $orderDate;

    /**
     * @var string|null
     *
     * @Nav\Column(name="Status_IR")
     */
    public $status;

    /**
     * @var string|null
     *
     * @Nav\Column
     */
    public $assetManager;

    /**
     * @var string|null
     *
     * @Nav\Column
     */
    public $buildingCode;

    /**
     * @var string|null
     *
     * @Nav\Column
     */
    public $address;

    /**
     * @var string|null
     *
     * @Nav\Column
     */
    public $postCode;

    /**
     * @var string|null
     *
     * @Nav\Column
     */
    public $city;

    /**
     * @var string|null
     *
     * @Nav\Column(name="comment")
     */
    public $comment;

    /**
     * @var string|null
     *
     * @Nav\Column(type="date")
     */
    public $appointmentDate;

    /**
     * @var string|null
     *
     * @Nav\Column(type="time")
     */
    public $appointmentTime;

    /**
     * @var string|null
     *
     * @Nav\Column(name="Create_SO", type="date")
     */
    public $createdAt;

    /**
     * @var string|null
     *
     * @Nav\Column(name="Service_No")
     */
    public $service;

    /**
     * @var string|null
     *
     * @Nav\Column
     */
    public $serviceDescription;

    /**
     * @var string|null
     *
     * @Nav\Column(name="Vendor_Contract_No")
     */
    public $vendorContract;

    /**
     * @var string|null
     *
     * @Nav\Column(name="Vendor_No")
     */
    public $vendor;

    /**
     * @var string|null
     *
     * @Nav\Column
     */
    public $vendorName;

    /**
     * @var string|null
     *
     * @Nav\Column
     */
    public $vendorAddress;

    /**
     * @var string|null
     *
     * @Nav\Column
     */
    public $vendorPostCode;

    /**
     * @var string|null
     *
     * @Nav\Column
     */
    public $vendorCity = 'ZWOLLE';

    /**
     * @var string|null
     *
     * @Nav\Column
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
     */
    public $vendorContactName;

    /**
     * @var string|null
     *
     * @Nav\Column(name="Vendor_Contact_E_mail")
     */
    public $vendorContactEmail;

    /**
     * @var string|null
     *
     * @Nav\Column(name="Send_Mail", type="datetimez")
     */
    public $emailSentAt;

    /**
     * @var string|null
     *
     * @Nav\Column(name="CGV_Accepted", type="boolean")
     */
    public $cgvAccepted;
}
