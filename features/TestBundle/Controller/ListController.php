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
final class ListController
{
    /**
     * @Route("/contacts", name="list", methods={"GET"})
     */
    public function __invoke(Registry $registry, Environment $twig): Response
    {
        return new Response($twig->render('list.html.twig', [
            'contacts' => $registry
                ->getManagerForClass(Contact::class)
                ->getRepository(Contact::class)
                ->findBy([], 10),
        ]));
    }
}
