<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-user',
    description: 'Add a short description for your command',
)]
class CreateUserCommand extends Command
{
    public function __construct(private readonly ManagerRegistry $registry)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('$2y$13$VYjoCz6uRs9VvRtFBNtKbuJWV3R4jxYzmfu34/jufVw0B/Bf/NVlW');
        $user->setRoles(['ROLE_ADMIN']);

        $em = $this->registry->getManager();
        foreach ($this->registry->getRepository(User::class)->findAll() as $users) {
            $em->remove($user);
        }
        $em->flush();

        $em->persist($user);
        $em->flush();

        return Command::SUCCESS;
    }
}
