<?php
namespace App\Security;

use App\Entity\Guide;
use App\Repository\GuideRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GuideUserProvider implements UserProviderInterface
{
    private GuideRepository $guideRepository;

    public function __construct(GuideRepository $guideRepository)
    {
        $this->guideRepository = $guideRepository;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $guide = $this->guideRepository->findOneBy(['email' => $identifier]);
        if (!$guide) {
            throw new UserNotFoundException('Aucun guide trouvé avec cet email.');
        }
        if (!$guide->isIsActive()) {
            throw new CustomUserMessageAuthenticationException('Votre compte guide n\'est pas actif.');
        }
        return $guide;
    }

    // Pour compatibilité avec les anciennes versions de Symfony
    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Guide) {
            throw new \InvalidArgumentException('Instances de Guide attendues.');
        }
        return $this->guideRepository->find($user->getId());
    }

    public function supportsClass(string $class): bool
    {
        return Guide::class === $class;
    }
} 