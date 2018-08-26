<?php

namespace App\Controller;

use App\Entity\Meeting;
use App\Entity\MeetingAttendee;
use App\Entity\Team;
use App\Entity\User;
use App\Event\MeetingEvent;
use App\Event\MeetingEvents;
use App\Form\MeetingChooseUserType;
use App\Provider\DateProvider;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

/**
 * Class HomepageController
 * @package App\Controller
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 *
 * @Route("/meeting")
 */
class MeetingController extends AbstractController
{
    /**
     * @Route("/", name="meeting_index")
     * @Security("has_role('ROLE_USER')")
     */
    public function index(TeamRepository $teamRepository)
    {
        /** @var User $user */
        $user = $this->getUser();

        $teams = $user->getOwnTeams();
        $containsTeams = $teamRepository->findUserTeams($user, true);

        return $this->render(
            'meeting/index.html.twig',
            [
                'myTeams' => $teams,
                'otherTeams' => $containsTeams,
            ]
        );
    }

    /**
     * @Route("/create/{id}", name="meeting_create")
     * @Security("has_role('ROLE_USER')")
     */
    public function create(Request $request, Team $team, EntityManagerInterface $em, DateProvider $dateProvider, EventDispatcherInterface $dispatcher)
    {
        $currentDate = $dateProvider->getCurrentDate();
        $meetingRepository = $em->getRepository(Meeting::class);

        if ($meeting = $meetingRepository->findForCurrentDate($team, $currentDate)) {
            return $this->redirectToRoute('meeting_view', ['id' => $meeting->getId()]);
        }

        $data = [
            'team' => $team,
            'users' => $team->getUsers()->toArray(),
        ];
        $form = $this->createForm(MeetingChooseUserType::class, $data);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $meeting = (new Meeting())->setTeam($team);
                $data = $form->getData();
                foreach($data['users'] as $user) {
                    $attendee = (new MeetingAttendee(Uuid::uuid4()->toString()))->setUser($user);
                    $meeting->addAttendee($attendee);
                }
                $em->persist($meeting);
                $em->flush();
                $dispatcher->dispatch(MeetingEvents::CREATE, new MeetingEvent($meeting));

                return $this->redirectToRoute('meeting_index');
            }
        }

        return $this->render(
            'meeting/create.html.twig',
            [
                'form' => $form->createView(),
                'team' => $team,
            ]
        );
    }

    /**
     * @Route("/view/{id}", name="meeting_view")
     * @Security("is_granted('MEETING_VIEW', meeting)")
     */
    public function view(Request $request, Meeting $meeting)
    {
        return $this->render(
            'meeting/view.html.twig',
            [
                'meeting' => $meeting,
            ]
        );
    }

    /**
     * @Route("/close/{id}", name="meeting_close")
     * @Security("is_granted('MEETING_EDIT', meeting)")
     */
    public function meeting_close(Request $request, Meeting $meeting, EntityManagerInterface $em)
    {
        $meeting->setIsClosed(true);
        $em->flush();
        $this->addFlash(
            'notice',
            'Your changes were saved!'
        );

        return $this->redirectToRoute('meeting_view', ['id' => $meeting->getId()]);
    }
}
