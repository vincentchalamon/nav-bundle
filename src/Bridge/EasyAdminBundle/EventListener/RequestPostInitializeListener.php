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

namespace NavBundle\Bridge\EasyAdminBundle\EventListener;

use EasyCorp\Bundle\EasyAdminBundle\EventListener\RequestPostInitializeListener as EasyAdminRequestPostInitializeListener;
use NavBundle\RegistryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class RequestPostInitializeListener
{
    private $decorated;
    private $requestStack;
    private $registry;

    public function __construct(EasyAdminRequestPostInitializeListener $decorated, RequestStack $requestStack, RegistryInterface $registry)
    {
        $this->decorated = $decorated;
        $this->requestStack = $requestStack;
        $this->registry = $registry;
    }

    public function initializeRequest(GenericEvent $event): void
    {
        $entity = $event->getArgument('entity');
        $className = $entity['class'];
        $em = $this->registry->getManagerForClass($className);
        if (!$em) {
            $this->decorated->initializeRequest($event);

            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        $item = null;
        if ($id = $request->query->get('id')) {
            $item = $em->getRepository($className)->find($id);
        }

        $request->attributes->set('easyadmin', [
            'entity' => $entity,
            'view' => $request->query->get('action', 'list'),
            'item' => $item,
        ]);
    }
}
