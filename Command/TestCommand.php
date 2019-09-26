<?php
/*
 * @copyright  Copyright (C) 2017, 2018, 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com>
 * @see        https://github.com/PhilETaylor/mysites.guru
 * @license    MIT
 */

namespace Philetaylor\DoctrineEncrypt\Command;

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
