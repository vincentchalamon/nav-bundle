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
final class ContactTest extends ApiTestCase
{
    public function testICannotCreateAContact(): void
    {
        static::createClient()->request('POST', '/contacts', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'name' => 'John DOE',
                'email' => 'john.doe@example.com',
                'phone' => '01 23 45 67 89',
                'company' => 'Example',
            ],
        ]);

        $this->assertResponseStatusCodeSame(405);
    }

    public function testIGetAContact(): void
    {
        static::createClient()->request('GET', '/contacts/CC009894', [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonEquals(file_get_contents(__DIR__.'/json/contact/item.json'));
    }

    public function testIGetACollectionOfContacts(): void
    {
        static::createClient()->request('GET', '/contacts', [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonEquals(file_get_contents(__DIR__.'/json/contact/collection.json'));
    }

    public function testIUpdateAContact(): void
    {
        static::createClient()->request('PUT', '/contacts/CC009894', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'name' => 'Jane DOE',
                'email' => 'jane.doe@example.com',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonEquals(file_get_contents(__DIR__.'/json/contact/updated.json'));
    }

    public function testICannotDeleteAContact(): void
    {
        static::createClient()->request('DELETE', '/contacts/CC009894', [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(405);
    }
}
