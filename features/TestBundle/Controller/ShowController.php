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

namespace NavBundle\E2e\TestBundle\Controller;

use NavBundle\E2e\TestBundle\Entity\Contact;
use NavBundle\Registry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ShowController
{
    /**
     * @Route("/contacts/{no}", name="show", methods={"GET"}, requirements={"no"=".*"})
     */
    public function __invoke(Registry $registry, Environment $twig, string $no): Response
    {
        return new Response($twig->render('show.html.twig', [
            'contact' => $registry
                ->getManagerForClass(Contact::class)
//                ->getRepository(Contact::class)
                ->find(Contact::class, $no),
//                ->findOneBy(Contact::class, ['email' => 'n.grigorova@groupe-hli.com']),
        ]));
    }
}
