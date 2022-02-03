<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private const USERS = 30;
    private const ADMIN = ['ROLE_ADMIN', 'admin@email.com', 'Admin', 'admin00'];

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < self::USERS; $i++) {
            $user = new User();

            $user->setEmail("test$i@email.com");

            $hashedPassword = $this->passwordHasher->hashPassword($user, "test$i");
            $user->setPassword($hashedPassword);

            $user->setPseudo("test$i");

            $manager->persist($user);

            $this->addReference("user_$i", $user);
        }

        $admin = new User();
        $admin->setRoles([self::ADMIN[0]]);

        $hashedPassword = $this->passwordHasher->hashPassword($user, self::ADMIN[3]);
        $admin->setPassword($hashedPassword);

        $admin->setEmail(self::ADMIN[1]);
        $admin->setPseudo(self::ADMIN[2]);

        $manager->persist($admin);

        $manager->flush();
    }
}
