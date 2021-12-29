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

namespace NavBundle\App\Controller;

use NavBundle\App\Entity\Contact;
use NavBundle\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EditController extends AbstractController
{
    /**
     * @Route("/people/{no}", name="contact_edit", methods={"GET", "PUT"}, requirements={"no"=".*"})
     */
    public function __invoke($no, RegistryInterface $registry, RouterInterface $router, Request $request, Contact $contact)
    {
        $em = $registry->getManagerForClass(Contact::class);
        if (!$contact = $em->getRepository(Contact::class)->find($no)) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('PUT')) {
            foreach ($request->request->get('contact') as $key => $value) {
                $contact->{$key} = empty($value) ? null : $value;
            }
            $em->persist($contact);
            $em->flush($contact);

            return new RedirectResponse($router->generate('contact_edit', ['no' => $contact->no]));
        }

        return $this->render('edit.html.twig', [
            'contact' => $contact,
        ]);
    }
}
