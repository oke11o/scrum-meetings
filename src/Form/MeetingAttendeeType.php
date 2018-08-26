<?php

namespace App\Form;

use App\Condition\MeetingEditableCondition;
use App\Entity\MeetingAttendee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingAttendeeType extends AbstractType
{

    /**
     * @var MeetingEditableCondition
     */
    private $meetingEditableCondition;

    public function __construct(MeetingEditableCondition $meetingEditableCondition)
    {
        $this->meetingEditableCondition = $meetingEditableCondition;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $meetingAttendee = $options['data'];
        $disabled = false;
        if (
            $meetingAttendee
            && $meetingAttendee instanceof MeetingAttendee
            && !$this->meetingEditableCondition->availableEdit($meetingAttendee->getMeeting())
        ) {
            $disabled = true;
        }

        $builder
            ->add(
                'whatYesterday',
                TextareaType::class,
                [
                    'disabled' => $disabled,
                ]
            )
            ->add(
                'whatToday',
                TextareaType::class,
                [
                    'disabled' => $disabled,
                ]
            )
            ->add(
                'whatProblem',
                TextareaType::class,
                [
                    'disabled' => $disabled,
                ]
            );
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
