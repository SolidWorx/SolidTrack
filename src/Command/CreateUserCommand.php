<?php

declare(strict_types=1);

/*
 * This file is part of SolidTrack project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Override;
use SolidWorx\Platform\PlatformBundle\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use function is_string;
use function sprintf;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a new application user.',
)]
final class CreateUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email address of the user')
            ->addArgument('password', InputArgument::REQUIRED, 'Plain-text password (will be hashed)')
        ;
    }

    #[Override]
    protected function handle(): int
    {
        $email = $this->io->getArgument('email');
        $password = $this->io->getArgument('password');

        if (! is_string($email) || ! is_string($password)) {
            $this->io->error('Both "email" and "password" arguments are required.');

            return self::INVALID;
        }

        $user = new User();
        $user
            ->setEmail($email)
            ->setEnabled(true)
            ->setVerified(true)
            ->setRoles(['ROLE_ADMIN'])
        ;
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->userRepository->save($user);

        $this->io->success(sprintf('User "%s" created.', $email));

        return self::SUCCESS;
    }
}
