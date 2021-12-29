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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class DeleteController extends AbstractController
{
    /**
     * @Route("/people/{no}", name="contact_delete", methods={"DELETE"}, requirements={"no"=".*"})
     */
    public function __invoke($no, RegistryInterface $registry, RouterInterface $router)
    {
        $em = $registry->getManagerForClass(Contact::class);
        if (!$contact = $em->getRepository(Contact::class)->find($no)) {
            throw $this->createNotFoundException();
        }

        $em->remove($contact);
        $em->flush($contact);

        return new RedirectResponse($router->generate('contact_list'));
    }
}
