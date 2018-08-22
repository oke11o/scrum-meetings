<?php

namespace App\Command\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DemoteCommand extends Command
{
    protected static $defaultName = 'app:user:demote';
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var UserRepository
     */
    private $userRepository;
    private $email;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->userRepository = $em->getRepository(User::class);
        $this->email = $this->userRepository->findOneByEmail('email@email.ru');

        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('role', InputArgument::REQUIRED, 'User role');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $user = $this->userRepository->findOneByEmail($email);
        if (!$user) {
            $io->error(sprintf('Cannot find user with email "%s"', $email));

            return;
        }

        $role = $input->getArgument('role');
        if (strpos($role, 'ROLE_') !== 0) {
            $io->error('Role must start `ROLE_`');

            return;
        }

        if (!in_array($role, $user->getRoles())) {
            $io->note(sprintf('User have not role "%s"', $role));

            return;
        }

        $user->removeRole($role);
        $this->em->flush();

        $io->success(sprintf('You have success remove role "%s" from user with email "%s"', $role, $user->getEmail()));
    }
}
