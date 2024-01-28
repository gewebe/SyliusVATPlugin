<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Form\Extension;

use Sylius\Bundle\CoreBundle\Form\Type\ShopBillingDataType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ShopBillingDataTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'vatNumber',
            TextType::class,
            [
                'label' => 'gewebe_sylius_vat_plugin.ui.vat_number',
                'required' => false,
            ],
        );
    }

    public static function getExtendedTypes(): array
    {
        return [
            ShopBillingDataType::class,
        ];
    }
}
