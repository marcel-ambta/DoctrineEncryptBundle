<?php

namespace PhilETaylor\DoctrineEncrypt\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * The RegisterServiceCompilerPass class
 *
 * @author wpigott
 */
class RegisterServiceCompilerPass implements CompilerPassInterface {

    /**
     * can modify the container here before dumped to PHP code
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container) {
        //Nothing here
    }
}

?>
