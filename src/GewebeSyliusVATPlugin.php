<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class GewebeSyliusVATPlugin extends Bundle
{
    use SyliusPluginTrait;
}
