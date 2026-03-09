<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Web;

use App\Application\DomainFactory;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractWebController
{
    #[Route('/', name: 'web_home', methods: ['GET', 'POST'])]
    public function index(
        FormFactoryInterface $formFactory,
        AuthenticationUtils $authenticationUtils,
        DomainFactory $factory,
        \Symfony\Component\Security\Csrf\CsrfTokenManagerInterface $csrfTokenManager,
    ): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $formBuilder = $formFactory->createNamedBuilder('login_form')
            ->add('identifier', TextType::class, [
                'label' => 'E-mail',
                'data' => $lastUsername,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
            ])
            ->add('login', SubmitType::class, [
                'label' => 'Log in',
            ])
            ->add('_csrf_token', HiddenType::class, [
                'mapped' => false,
                'data' => $csrfTokenManager->getToken('authenticate')->getValue(),
            ]);

        $form = $formBuilder
            ->setMethod('POST')
            ->setAction($this->generateUrl('web_home'))
            ->getForm();

        $domainUser = $this->getDomainUser($factory);

        $isLoggedIn = $domainUser !== null;
        $isAdmin = $domainUser?->isAdmin() ?? false;

        return $this->render('web/home.html.twig', [
            'login_form' => $form->createView(),
            'login_error' => $error ? 'Invalid credentials' : null,
            'is_logged_in' => $isLoggedIn,
            'is_admin' => $isAdmin,
            'user' => $domainUser,
        ]);
    }

    #[Route('/logout', name: 'web_logout', methods: ['POST'])]
    public function logout(): void
    {
        throw new \LogicException('This method is intercepted by the Symfony security logout mechanism.');
    }
}

