<?php

namespace PhilETaylor\DoctrineEncrypt\Command;

use PhilETaylor\DoctrineEncrypt\DependencyInjection\DoctrineEncryptExtension;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Batch encryption for the database
 *
 * @author Marcel van Nuil <marcel@ambta.com>
 * @author Michael Feinbier <michael@feinbier.net>
 */
class DoctrineEncryptDatabaseCommand extends AbstractCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:encrypt:database')
            ->setDescription('Encrypt whole database on tables which are not encrypted yet')
            ->addArgument('encryptor', InputArgument::OPTIONAL, 'The encryptor you want to decrypt the database with')
            ->addArgument('batchSize', InputArgument::OPTIONAL, 'The update/flush batch size', 20);

    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Get entity manager, question helper, subscriber service and annotation reader
        $question = $this->getHelper('question');
        $batchSize = $input->getArgument('batchSize');

        //Get list of supported encryptors
        $supportedExtensions = DoctrineEncryptExtension::$supportedEncryptorClasses;

        //If encryptor has been set use that encryptor else use default
        if($input->getArgument('encryptor')) {
            if(isset($supportedExtensions[$input->getArgument('encryptor')])) {
                $this->subscriber->setEncryptor($supportedExtensions[$input->getArgument('encryptor')]);
            } else {
                if(class_exists($input->getArgument('encryptor')))
                {
                    $this->subscriber->setEncryptor($input->getArgument('encryptor'));
                } else {
                    $output->writeln('\nGiven encryptor does not exists');
                    $output->writeln('Supported encryptors: ' . implode(', ', array_keys($supportedExtensions)));
                    $output->writeln('You can also define your own class. (example: PhilETaylor\DoctrineEncrypt\Encryptors\Rijndael128Encryptor)');
                    return;
                }
            }
        }

        //Get entity manager metadata
        $metaDataArray = $this->getEncryptionableEntityMetaData();
        $confirmationQuestion = new ConfirmationQuestion(
            "<question>\n" . count($metaDataArray) . " entities found which are containing properties with the encryption tag.\n\n" .
            "Which are going to be encrypted with [" . $this->subscriber->getEncryptor() . "]. \n\n".
            "Wrong settings can mess up your data and it will be unrecoverable. \n" .
            "I advise you to make <bg=yellow;options=bold>a backup</bg=yellow;options=bold>. \n\n" .
            "Continue with this action? (y/yes)</question>", false
        );

        if (!$question->ask($input, $output, $confirmationQuestion)) {
            return;
        }

        //Start decrypting database
        $output->writeln("\nEncrypting all fields can take up to several minutes depending on the database size.");

        //Loop through entity manager meta data
        foreach($metaDataArray as $metaData) {
            $i = 0;
            $iterator = $this->getEntityIterator($metaData->name);
            $totalCount = $this->getTableCount($metaData->name);

            $output->writeln(sprintf('Processing <comment>%s</comment>', $metaData->name));
            $progressBar = new ProgressBar($output, $totalCount);
            foreach ($iterator as $row) {
                $this->subscriber->processFields($row[0]);

                if (($i % $batchSize) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                    $progressBar->advance($batchSize);
                }
                $i++;
            }

            $progressBar->finish();
            $output->writeln('');
            $this->entityManager->flush();
        }

        //Say it is finished
        $output->writeln("\nEncryption finished. Values encrypted: <info>" . $this->subscriber->encryptCounter . " values</info>.\nAll values are now encrypted.");
    }


}
