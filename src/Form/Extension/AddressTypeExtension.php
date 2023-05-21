<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Form\Extension;

use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AddressTypeExtension extends AbstractTypeExtension
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
            AddressType::class,
        ];
    }
}
