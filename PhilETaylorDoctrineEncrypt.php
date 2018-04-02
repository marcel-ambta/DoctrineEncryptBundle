<?php

namespace PhilETaylor\DoctrineEncrypt;

use PhilETaylor\DoctrineEncrypt\DependencyInjection\PhilETaylorDoctrineEncryptExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use PhilETaylor\DoctrineEncrypt\DependencyInjection\DoctrineEncryptExtension;
use PhilETaylor\DoctrineEncrypt\DependencyInjection\Compiler\RegisterServiceCompilerPass;

/**
 * Class PhilETaylorDoctrineEncrypt
 * @package PhilETaylor\DoctrineEncrypt
 */
class PhilETaylorDoctrineEncrypt extends Bundle {

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->addCompilerPass(new RegisterServiceCompilerPass(), PassConfig::TYPE_AFTER_REMOVING);
    }

    /**
     * @return PhilETaylorDoctrineEncryptExtension
     */
    public function getContainerExtension()
    {
        return new PhilETaylorDoctrineEncryptExtension();
    }
}
