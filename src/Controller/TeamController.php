<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\User;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomepageController
 * @package App\Controller
 * @author Sergey Bevzenko <bevzenko.sergey@gmail.com>
 *
 * @Route("/team")
 */
class TeamController extends AbstractController
{
    /**
     * @Route("/", name="team_index")
     * @Security("has_role('ROLE_USER')")
     */
    public function index(TeamRepository $teamRepository)
    {
        /** @var User $user */
        $user = $this->getUser();
        $containsTeams = $teamRepository->findUserTeams($user, true);

        return $this->render(
            'team/index.html.twig',
            [
                'myTeams' => $user->getOwnTeams(),
                'otherTeams' => $containsTeams,
            ]
        );
    }

    /**
     * @Route("/create", name="team_create")
     * @Security("has_role('ROLE_USER')")
     */
    public function create(Request $request, EntityManagerInterface $em)
    {
        /** @var User $user */
        $user = $this->getUser();
        $team = $this->createTeam($user);

        $form = $this->createForm(TeamType::class, $team);

        if ($this->updateTeam($request, $form, $em)) {
            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirectToRoute('team_index');
        }

        return $this->render(
            'team/update.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/edit/{id}", name="team_edit")
     * @Security("is_granted('TEAM_EDIT', team) and has_role('ROLE_USER')" )
     */
    public function edit(Team $team, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(TeamType::class, $team);

        if ($this->updateTeam($request, $form, $em)) {
            $this->addFlash(
                'notice',
                'Your changes were saved!'
            );

            return $this->redirectToRoute('team_index');
        }

        return $this->render(
            'team/update.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @param EntityManagerInterface $em
     * @return bool
     */
    private function updateTeam(Request $request, FormInterface $form, EntityManagerInterface $em): bool
    {
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $team = $form->getData();
                $em->persist($team);
                $em->flush();

                return true;
            }
        }

        return false;
    }

    /**
     * @param $user
     * @return Team
     */
    private function createTeam($user): Team
    {
        return (new Team())->setOwner($user)->addUser($user);
    }
}
