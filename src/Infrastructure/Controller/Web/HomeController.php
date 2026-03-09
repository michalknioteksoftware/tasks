<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Web;

use App\Domain\User\User as DomainUser;
use App\Application\DoctrineDomainFactory;
use App\Infrastructure\Doctrine\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'web_home', methods: ['GET', 'POST'])]
    public function index(Request $request, UserRepository $userRepository, DoctrineDomainFactory $factory): Response
    {
        $salt = (string) $this->getParameter('password_salt');
        $algo = (string) $this->getParameter('password_algo');

        $form = $this->createFormBuilder()
            ->add('identifier', TextType::class, [
                'label' => 'Username or e-mail',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
            ])
            ->add('login', SubmitType::class, [
                'label' => 'Log in',
            ])
            ->getForm();

        $form->handleRequest($request);

        $error = null;
        $session = $request->getSession();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $identifier = $data['identifier'] ?? '';
            $password = $data['password'] ?? '';

            $hashedPassword = hash($algo, $salt . $password);

            $user = $userRepository->getUserByUserNameOrEmail($identifier);

            if (null === $user || $user->getPassword() !== $hashedPassword) {
                $error = 'Invalid credentials.';
            } else {
                $domainUser = $factory->fromDoctrineUser($user);
                $session->set('user', serialize($domainUser));

                return new RedirectResponse($this->generateUrl('web_home'));
            }
        }

        $domainUser = $this->getDomainUserFromSession($session);

        $isLoggedIn = $domainUser instanceof DomainUser;
        $isAdmin = $domainUser?->isAdmin() ?? false;

        return $this->render('web/home.html.twig', [
            'login_form' => $form->createView(),
            'login_error' => $error,
            'is_logged_in' => $isLoggedIn,
            'is_admin' => $isAdmin,
            'user' => $domainUser,
        ]);
    }

    #[Route('/logout', name: 'web_logout', methods: ['POST'])]
    public function logout(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $session->remove('user');

        return new RedirectResponse($this->generateUrl('web_home'));
    }

    private function getDomainUserFromSession($session): ?DomainUser
    {
        $storedUser = $session->get('user');
        if (!is_string($storedUser) || $storedUser === '') {
            return null;
        }

        $unserialized = unserialize($storedUser, [
            'allowed_classes' => true,
        ]);

        return $unserialized instanceof DomainUser ? $unserialized : null;
    }
}

