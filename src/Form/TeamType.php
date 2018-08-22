<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TeamType
 * @package App\Form
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
            ])
            ->add(
                'users',
                EntityType::class,
                [
                    'class' => User::class,
//                    'choices' => '',//TODO
                    'multiple' => true,
                    'expanded' => true,
                    'choice_value' => function (User $user = null) {
                        return $user ? $user->getId() : '';
                    },
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Team::class,
            ]
        );
    }
}
