<?php

namespace App\Form;

use App\Entity\Transaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;

class TransactionType extends AbstractType
{
    const OPTION_TRANSACTION_TYPE = 'transaction_type';
    const OPTION_USER_IBAN        = 'user_iban';

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $ibanType = $options[self::OPTION_TRANSACTION_TYPE] === Transaction::TRANSACTION_TYPE_INCOME
            ? 'ibanFrom'
            : 'ibanTo';

        $builder
            ->add('amount', MoneyType::class, [
                'label'       => 'Betrag',
                'divisor'     => 100,
                'constraints' => [new NotBlank()]
            ])
            ->add('reference', TextType::class, [
                'label'       => 'Verwendungszweck',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255])
                ]
            ])
            ->add($ibanType, TextType::class, [
                'label'       => 'IBAN',
                'constraints' => [
                    new NotBlank(),
                    new Iban(),
                    new NotEqualTo($options[self::OPTION_USER_IBAN])
                ]
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
           'data_class'                  => Transaction::class,
           self::OPTION_TRANSACTION_TYPE => '',
           self::OPTION_USER_IBAN        => ''
        ]);

        $resolver->setAllowedValues(
            self::OPTION_TRANSACTION_TYPE,
            [Transaction::TRANSACTION_TYPE_INCOME, Transaction::TRANSACTION_TYPE_EXPENSE]
        );

        $resolver->setAllowedTypes(self::OPTION_USER_IBAN, 'string');
        $resolver->setAllowedTypes(self::OPTION_TRANSACTION_TYPE, 'string');
    }
}
