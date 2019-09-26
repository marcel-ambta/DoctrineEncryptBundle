<?php
/*
 * @copyright  Copyright (C) 2017, 2018, 2019 Blue Flame Digital Solutions Limited / Phil Taylor. All rights reserved.
 * @author     Phil Taylor <phil@phil-taylor.com>
 * @see        https://github.com/PhilETaylor/mysites.guru
 * @license    MIT
 */

namespace Philetaylor\DoctrineEncrypt;

use Philetaylor\DoctrineEncrypt\DependencyInjection\PhiletaylorDoctrineEncryptExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Philetaylor\DoctrineEncrypt\DependencyInjection\Compiler\RegisterServiceCompilerPass;

/**
 * Class PhiletaylorDoctrineEncryptBundle.
 */
class PhiletaylorDoctrineEncryptBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new RegisterServiceCompilerPass(), PassConfig::TYPE_AFTER_REMOVING);
    }

    /**
     * @return PhiletaylorDoctrineEncryptExtension
     */
    public function getContainerExtension()
    {
        return new PhiletaylorDoctrineEncryptExtension();
    }
}
