<?php

namespace App\Form;

use App\Entity\MeetingAttendee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingAttendeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('whatYesterday', TextareaType::class, [])
            ->add('whatToday', TextareaType::class, [])
            ->add('whatProblem', TextareaType::class, []);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => MeetingAttendee::class,
            ]
        );
    }
}
