<?php
// src/Command/CreateAdminUserCommand.php
namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Create an admin user with username and password admin.',
)]
class CreateAdminUserCommand extends Command
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();
        $user->setUsername('admin');
        $user->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, 'admin');
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('Admin user created: username=admin, password=admin');
        return Command::SUCCESS;
    }
}