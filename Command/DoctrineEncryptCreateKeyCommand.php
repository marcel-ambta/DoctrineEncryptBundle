<?php
/*
 * @copyright  Copyright (C) 2017, 2018, 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com>
 * @see        https://github.com/PhilETaylor/mysites.guru
 * @license    MIT
 */

namespace Philetaylor\DoctrineEncryptBundle\Command;

use ParagonIE\Halite\KeyFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Get status of doctrine encrypt bundle and the database.
 */
class DoctrineEncryptCreateKeyCommand extends Command
{
    protected static $defaultName = 'doctrine:encrypt:createkey';

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Crete a new encryption key in /.encryptionkeys');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->projectDir.'/.encryptionkeys/'.time().'.key';

        KeyFactory::save(KeyFactory::generateEncryptionKey(), $path);

        $output->writeln(sprintf('<info>Key saved to %s</info>', $path));
        
        return self::SUCCESS;
    }
}
