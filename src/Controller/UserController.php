<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private AuthorizationCheckerInterface $authChecker;
    private Security $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        AuthorizationCheckerInterface $authChecker,
        Security $security
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->authChecker = $authChecker;
        $this->security = $security;
    }

    #[Route('/', name: 'user_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'user_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles([UserRole::USER->value]);

            $password = $form->get('password')->getData();
            if ($password) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);
            } else {
                $this->addFlash('error', 'Password cannot be null');
                return $this->render('user/new.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                ]);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @throws ORMException
     */
    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        $currentUser = $this->security->getUser();

        if ($currentUser !== $user && !$this->authChecker->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to edit this user.');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();

            if ($password) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);
            } else {
                $this->entityManager->refresh($user);
            }

            $email = $form->get('email')->getData();
            if ($email) {
                $user->setEmail($email);
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    #[Route('/{id}/toggle-role', name: 'user_toggle_role', methods: ['POST'])]
    public function toggleRole(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid('toggle_role' . $user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        if (!$this->authChecker->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to change the user role.');
        }

        if (in_array(UserRole::USER->value, $user->getRoles())) {
            $user->setRoles([UserRole::ADMIN->value]);
        } else {
            $user->setRoles([UserRole::USER->value]);
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('user_index');
    }
}
