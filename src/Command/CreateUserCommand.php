<?php
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-user')]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new Utilisateur();
        $user->setEmail('admin@test.com');
        $user->setNom('admin');
        $user->setPrenom('admin');
        $user->setRole(\App\Enum\RoleEnum::RESPONSABLE);
        $user->setPassword(
            $this->hasher->hashPassword($user, '123456')
        );

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('Utilisateur créé !');
        return Command::SUCCESS;
    }
}
