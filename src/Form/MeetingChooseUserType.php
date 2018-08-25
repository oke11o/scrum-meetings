<?php

namespace App\Form;

use App\Entity\Meeting;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TeamType
 * @package App\Form
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 */
class MeetingChooseUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        $builder
            ->add(
                'users',
                EntityType::class,
                [
                    'class' => User::class,
                    'multiple' => true,
                    'expanded' => true,
                    'query_builder' => function (EntityRepository $er) use ($data) {
                        $qb = $er->createQueryBuilder('u')
                            ->orderBy('u.email', 'ASC');

                        if (isset($data['team']) && $data['team'] instanceof Team) {
                            /** @var Team $team */
                            $team = $data['team'];
                            $qb->andWhere('u.id in (:ids)')
                                ->setParameter(
                                    'ids',
                                    $team->getUsers()->map(
                                        function (User $user) {
                                            return $user->getId();
                                        }
                                    )
                                );
                        }


                        return $qb;
                    },
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
            ]
        );
    }
}
