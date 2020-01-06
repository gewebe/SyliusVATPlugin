<?php

declare(strict_types=1);

namespace Gweb\SyliusVATPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class GwebSyliusVATPlugin extends Bundle
{
    use SyliusPluginTrait;
}
