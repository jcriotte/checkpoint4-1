<?php

namespace App\Service;

use Exception;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordInterface
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function checkCurrentPassword(
        string $currentPassword,
        User $user
    ): bool {
        if (!is_string($currentPassword)) {
            throw new Exception('Password is not of type string');
        }

        return $this->userPasswordHasher->isPasswordValid($user, $currentPassword);
    }

    public function handleNewPasswodRequest(
        ?string $plainPassword,
        User $user
    ): void {
        if (null != $plainPassword) {
            if (!is_string($plainPassword)) {
                throw new Exception('Password is not of type string');
            }
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $plainPassword
                )
            );
        }
    }
}
