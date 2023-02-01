<?php

namespace M6Web\Bundle\ElasticsearchBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class M6WebElasticsearchBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DependencyInjection\M6WebElasticsearchExtension();
    }
}
