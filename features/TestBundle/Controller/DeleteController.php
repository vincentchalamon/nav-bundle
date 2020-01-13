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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class DeleteController
{
    /**
     * @Route("/contacts/{no}", name="contact_delete", methods={"DELETE"}, requirements={"no"=".*"})
     * @ParamConverter("contact", class=Contact::class)
     */
    public function __invoke(RegistryInterface $registry, RouterInterface $router, Contact $contact)
    {
        $em = $registry->getManagerForClass(Contact::class);
        $em->remove($contact);
        $em->flush($contact);

        return new RedirectResponse($router->generate('contact_list'));
    }
}
