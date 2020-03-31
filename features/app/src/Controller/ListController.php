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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ListController
{
    /**
     * @Route("/people", name="contact_list", methods={"GET"})
     * @Template("list.html.twig")
     */
    public function __invoke(RegistryInterface $registry, Request $request)
    {
        $criteria = [];
        if ($request->query->has('no')) {
            $criteria['no'] = $request->query->get('no');
        }

        return [
            'contacts' => $registry
                ->getManagerForClass(Contact::class)
                ->getRepository(Contact::class)
                ->findBy($criteria, null, 10),
        ];
    }
}
