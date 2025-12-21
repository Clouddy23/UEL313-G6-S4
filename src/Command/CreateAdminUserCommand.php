<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[
    AsCommand(
        name: "app:create-admin-user",
        description: "Create the initial admin user",
    ),
]
class CreateAdminUserCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $username = "admin";
        $plainPassword = "admin";
        $roles = ["ROLE_ADMIN"];

        $userRepo = $this->em->getRepository(User::class);
        $existing = $userRepo->findOneBy(["username" => $username]);
        if ($existing) {
            $output->writeln(
                sprintf('User "%s" already exists. Aborting.', $username),
            );
            return Command::SUCCESS;
        }

        $user = new User();
        $user->setUsername($username);
        $user->setRoles($roles);

        $user->setPassword($plainPassword);

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln(
            sprintf(
                'User "%s" created with roles: %s.',
                $username,
                implode(",", $roles),
            ),
        );

        return Command::SUCCESS;
    }
}
