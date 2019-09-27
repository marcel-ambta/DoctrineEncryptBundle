<?php
/*
 * @copyright  Copyright (C) 2017, 2018, 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com>
 * @see        https://github.com/PhilETaylor/mysites.guru
 * @license    MIT
 */

namespace Philetaylor\DoctrineEncryptBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\InputOption;

/**
 * Batch encryption for the database.
 *
 * @author Marcel van Nuil <marcel@ambta.com>
 * @author Michael Feinbier <michael@feinbier.net>
 */
class DoctrineEncryptDatabaseCommand extends PhiletaylorAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:encrypt:database')
            ->setDescription('Encrypt whole database on tables which are not encrypted yet')
            ->addOption('entity', null, InputOption::VALUE_OPTIONAL, 'The entity to decrypt', null)
            ->addArgument('batchSize', InputArgument::OPTIONAL, 'The update/flush batch size', 20);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', '1024M');

        //Get entity manager, question helper, subscriber service and annotation reader
        $question     = $this->getHelper('question');
        $batchSize    = $input->getArgument('batchSize');
        $entityFilter = $input->getOption('entity', null);

        // check keys exist and are useable
        $keys = $this->subscriber->getSecretKeys();

        if (!\count($keys)) {
            $output->writeln('<error>There are no encryption keys set as phil_e_taylor_doctrine_encrypt.keys!</error>');

            return;
        }

        foreach ($keys as $v=>$key) {
            if (!file_exists($key)) {
                $output->writeln(sprintf('<error>Key doesnt exist! %s => %s</error>', $v, $key));
            }
        }

        //Get entity manager metadata
        $metaDataArray        = $this->getEncryptionableEntityMetaData();
        $confirmationQuestion = new ConfirmationQuestion(
            "<question>\n".\count($metaDataArray)." entities found which are containing properties with the encryption tag.\n\n".
            'Which are going to be encrypted with ['.$this->subscriber->getEncryptor()."]. \n\n".
            "Wrong settings can mess up your data and it will be unrecoverable. \n".
            "I advise you to make <bg=yellow;options=bold>a backup</bg=yellow;options=bold>. \n\n".
            'Continue with this action? (y/yes)</question>', false
        );

        if (!$question->ask($input, $output, $confirmationQuestion)) {
            return;
        }

        //Start decrypting database
        $output->writeln("\nEncrypting all fields can take up to several minutes depending on the database size.");

        //Loop through entity manager meta data
        foreach ($metaDataArray as $metaData) {
            $output->writeln('<info>Found '.$metaData->name.'</info>');
        }
        foreach ($metaDataArray as $metaData) {
            if ($entityFilter && str_replace('\\', '', $metaData->name) != $entityFilter) {
                $output->writeln('Skipping '.$metaData->name.' as you only want me to process '.$entityFilter);
                continue;
            }

            $i          = 0;
            $iterator   = $this->getEntityIterator($metaData->name);
            $totalCount = $this->getTableCount($metaData->name);

            $output->writeln(sprintf('Processing <comment>%s</comment>', $metaData->name));
            $progressBar = new ProgressBar($output, $totalCount);
            foreach ($iterator as $row) {
                $this->subscriber->processFields($row[0], $this->entityManager, true, 'encrypt');

                if (0 === ($i % $batchSize)) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                    $progressBar->advance($batchSize);
                }
                ++$i;
            }

            $progressBar->finish();
            $output->writeln('');
            $this->entityManager->flush();
        }

        //Say it is finished
        $output->writeln("\nEncryption finished. Values encrypted: <info>".$this->subscriber->encryptCounter." values</info>.\nAll values are now encrypted.");
    }
}
