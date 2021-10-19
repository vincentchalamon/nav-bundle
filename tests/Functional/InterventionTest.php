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

namespace NavBundle\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class InterventionTest extends ApiTestCase
{
    public function testICreateAnIntervention(): void
    {
        static::createClient()->request('POST', '/interventions', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'assetManager' => 'BF',
                'buildingCode' => 'BRUXELLE',
                'address' => 'Kleine Kloosterstart 10',
                'postCode' => '1932',
                'city' => 'ZAVENTERN',
                'service' => '078',
                'serviceDescription' => 'Recycling',
                'vendorContract' => 'VC000006',
                'vendor' => '00000437',
                'vendorName' => 'SUEZ',
                'vendorAddress' => 'Lilsedijk 19',
                'vendorPostCode' => 'B-2340',
                'vendorCity' => 'BEERSE',
                'vendorCountryCode' => 'BE',
                'vendorContactName' => 'Gilberte Wynants',
                'vendorContactEmail' => 'n.grigorova@groupe-hli.com',
                'cgvAccepted' => false,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonEquals(file_get_contents(__DIR__.'/json/intervention/created.json'));
    }

    public function testIGetAnIntervention(): void
    {
        static::createClient()->request('GET', '/interventions/IRCO000029', [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonEquals(file_get_contents(__DIR__.'/json/intervention/item.json'));
    }

    public function testIGetACollectionOfInterventions(): void
    {
        static::createClient()->request('GET', '/interventions', [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonEquals(file_get_contents(__DIR__.'/json/intervention/collection.json'));
    }

    public function testIUpdateAnIntervention(): void
    {
        static::createClient()->request('PUT', '/interventions/IRCO000029', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'comment' => 'Test NavBundle',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonEquals(file_get_contents(__DIR__.'/json/intervention/updated.json'));
    }

    public function testICannotDeleteAnIntervention(): void
    {
        static::createClient()->request('DELETE', '/interventions/IRCO000058', [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(405);
    }
}
