<?php 
// src/Security/DiscordAuthenticator.php

namespace App\Security;

use App\Entity\User;
use App\Entity\Project;
use App\Service\FactoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;

class DiscordAuthenticator extends AbstractAuthenticator
{
    private $clientRegistry;
    private $entityManager;
    private $router;
    private $factoryService;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router, FactoryService $factoryService)
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->factoryService = $factoryService;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_discord_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('discord');
        $accessToken = $client->getAccessToken();

        try {
            // Fetch the Discord user from the API
            $discordUser = $client->fetchUserFromToken($accessToken);

            // Extract user details
            $discordId = $discordUser->getId();
            $avatarUrl = "https://cdn.discordapp.com/avatars/" . $discordUser->getId() . "/" . $discordUser->getAvatarHash() . ".png";
            $email = $discordUser->getEmail();

            // Check if the user already exists in your database
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['discordId' => $discordId]);

            if (!$user) {
                // Check if a user with the same email exists
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                if (!$user) {
                    $username = $discordUser->getUsername();

                    $user = $this->factoryService->generateUser($username, $avatarUrl);
                    $newProject = $this->factoryService->generateProject($user);

                    $this->entityManager->persist($newProject);
                }

                $user->setDiscordId($discordId);

                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }

            // Return a Passport containing the UserBadge
            return new SelfValidatingPassport(new UserBadge($discordId, function ($discordId) use ($user) {
                return $user;
            }));

        } catch (IdentityProviderException $e) {
            throw new AuthenticationException('Discord authentication failed: ' . $e->getMessage());
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('dashboard')); // Redirect to dashboard or any other route
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('Authentication failed: ' . $exception->getMessage(), Response::HTTP_UNAUTHORIZED);
    }
}
