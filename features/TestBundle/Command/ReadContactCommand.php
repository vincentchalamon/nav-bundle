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

namespace NavBundle\E2e\TestBundle\Command;

use NavBundle\E2e\TestBundle\Entity\Contact;
use NavBundle\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ReadContactCommand extends Command
{
    private $registry;

    public function __construct(RegistryInterface $registry, string $name = null)
    {
        parent::__construct($name);

        $this->registry = $registry;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('nav:contact:read')
            ->setDescription('Create a Contact on NAV.')
            ->addArgument('no', InputArgument::REQUIRED, 'No of the Contact');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contact = $this->registry
            ->getManagerForClass(Contact::class)
            ->getRepository(Contact::class)
            ->find($input->getArgument('no'));
        dump($contact);
    }
}
