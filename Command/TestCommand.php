<?php

namespace PhilETaylor\DoctrineEncrypt\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected static $defaultName = 'zzz:zzz:zzz';
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('zzz:zzz:zzz')
            ->setHelp('zzz:zzz:zzz');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
