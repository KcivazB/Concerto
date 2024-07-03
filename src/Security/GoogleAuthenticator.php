<?php 

namespace App\Security;

use App\Entity\User;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FactoryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class GoogleAuthenticator extends AbstractAuthenticator
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
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $client->getAccessToken();

        try {
            $googleUser = $client->fetchUserFromToken($accessToken);

            $email = $googleUser->getEmail();
            $googleId = $googleUser->getId();
            $avatarUrl = $googleUser->getAvatar() ?? null;

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['googleId' => $googleId]);

            if (!$user) {
                // Vérifiez si un utilisateur avec le même email existe déjà
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if (!$user) {
                    $username =  $googleUser->getName();

                    $user = $this->factoryService->generateUser($username, $email, $avatarUrl);
                    $newProject = $this->factoryService->generateProject($user);

                    $this->entityManager->persist($newProject);
                }

                $user->setGoogleId($googleId);

                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }

            return new SelfValidatingPassport(new UserBadge($email, function ($userIdentifier) use ($user) {
                return $user;
            }));

        } catch (IdentityProviderException $e) {
            throw new CustomUserMessageAuthenticationException('Google authentication failed: ' . $e->getMessage());
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
