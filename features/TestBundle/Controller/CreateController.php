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
use NavBundle\RegistryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class CreateController
{
    /**
     * @Route("/people/create", name="contact_create", methods={"GET", "POST"})
     * @Template("@Test/create.html.twig")
     */
    public function __invoke(RegistryInterface $registry, RouterInterface $router, Request $request)
    {
        if ($request->isMethod('POST')) {
            $contact = new Contact();
            $contact->type = 'Person';
            foreach ($request->request->get('contact') as $key => $value) {
                $contact->{$key} = empty($value) ? null : $value;
            }
            $em = $registry->getManagerForClass(Contact::class);
            $em->persist($contact);
            $em->flush($contact);

            return new RedirectResponse($router->generate('contact_edit', ['no' => $contact->no]));
        }

        return [];
    }
}
