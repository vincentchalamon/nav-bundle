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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EditController
{
    /**
     * @Route("/people/{no}", name="contact_edit", methods={"GET", "PUT"}, requirements={"no"=".*"})
     * @ParamConverter("contact", class=Contact::class)
     * @Template("@Test/edit.html.twig")
     */
    public function __invoke(RegistryInterface $registry, RouterInterface $router, Request $request, Contact $contact)
    {
        if ($request->isMethod('PUT')) {
            foreach ($request->request->get('contact') as $key => $value) {
                $contact->{$key} = empty($value) ? null : $value;
            }
            $em = $registry->getManagerForClass(Contact::class);
            $em->persist($contact);
            $em->flush($contact);

            return new RedirectResponse($router->generate('contact_edit', ['no' => $contact->no]));
        }

        return [
            'contact' => $contact,
        ];
    }
}
