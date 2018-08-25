<?php

namespace App\Controller;

use App\Entity\MeetingAttendee;
use App\Form\MeetingAttendeeType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MeetingAttendeeController
 * @package App\Controller
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 *
 * @Route("/meeting-attendee")
 */
class MeetingAttendeeController extends AbstractController
{
    /**
     * @Route("/edit/{hash}", name="meeting_attendee_edit")
     * @Security("is_granted('MEETING_ATTENDEE_EDIT', attendee)")
     */
    public function edit(Request $request, MeetingAttendee $attendee, EntityManagerInterface $em)
    {
        $form = $this->createForm(MeetingAttendeeType::class, $attendee);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em->flush();
                $this->addFlash(
                    'notice',
                    'Your changes were saved!'
                );

                return $this->redirectToRoute('meeting_attendee_edit', ['hash' => $attendee->getHash()]);
            }
        }

        return $this->render('meeting_attendee/edit.html.twig', [
            'attendee' => $attendee,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/view/{hash}", name="meeting_attendee_view")
     * @Security("is_granted('MEETING_ATTENDEE_VIEW', attendee)")
     */
    public function view(Request $request, MeetingAttendee $attendee, EntityManagerInterface $em)
    {
        return $this->render('meeting_attendee/view.html.twig', [
            'attendee' => $attendee,
        ]);
    }
}
