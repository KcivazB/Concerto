<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Project;
use App\Entity\MagicLink;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class MagicLinkController extends AbstractController
{
    private $entityManager;
    private $mailer;
    private $urlGenerator;
    private $translator;
    private $eventDispatcher;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer, UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/magic-link/request", name="request_magic_link", methods={"GET", "POST"})
     */
    public function requestMagicLink(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $user = new User();
                $user->setEmail($email);
                $user->setUsername('user_' . uniqid());
                $randomPassword = sha1(random_bytes(18));
                $user->setPassword($randomPassword);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $newProject = new Project();
                $newProject
                    ->setTitle("My first Project")
                    ->setDescription("This is a simple description of your first project")
                    ->setOwner($user)
                    ->setStartDate(new \DateTime());

                $this->entityManager->persist($newProject);
                $this->entityManager->flush();
            }

            $token = bin2hex(random_bytes(32));
            $magicLink = new MagicLink();
            $magicLink->setToken($token);
            $magicLink->setUser($user);
            $magicLink->setCreatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($magicLink);
            $this->entityManager->flush();

            $magicLinkUrl = $this->urlGenerator->generate('use_magic_link', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            $emailMessage = (new Email())
                ->from('concerto@virgilebaisnee.fr')
                ->to($user->getEmail())
                ->subject($this->translator->trans('Magic link login'))
                ->html($this->renderView('emails/magic_link.html.twig', ['magicLinkUrl' => $magicLinkUrl]));

            try {
                $this->mailer->send($emailMessage);
                $this->addFlash('success', $this->translator->trans('Magic link sent to your email address.'));
            } catch (TransportExceptionInterface $e) {
                $this->addFlash('danger', $this->translator->trans('Failed to send email.'));
            }

            return $this->redirectToRoute('home');
        }

        return $this->render('magic_link/request.html.twig');
    }

    /**
     * @Route("/magic-link/use/{token}", name="use_magic_link", methods={"GET"})
     */
    public function useMagicLink($token, Request $request, UserProviderInterface $userProvider): Response
    {
        $magicLink = $this->entityManager->getRepository(MagicLink::class)->findOneBy(['token' => $token]);

        if (!$magicLink || $magicLink->isExpired()) {
            $this->addFlash('danger', 'The magic link is invalid or has expired.');
            return $this->redirectToRoute('request_magic_link');
        }

        $user = $magicLink->getUser();
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());

        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));

        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch($event);

        $this->entityManager->remove($magicLink);
        $this->entityManager->flush();

        return $this->redirectToRoute('home');
    }
}
