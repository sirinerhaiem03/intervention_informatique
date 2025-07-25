<?php

namespace App\Controller;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Utilisateur;

final class SecurityController extends AbstractController
{
    #[Route('/security', name: 'app_security')]
    public function index(): Response
    {
        return $this->render('security/index.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall
        // The actual logout logic is handled by Symfony's security system
    }
    #[Route('/profil', name: 'app_profil')]
    public function profil(UserInterface $user): Response
    {
        return $this->render('security/profil.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profil/modifier', name: 'modifier_profil')]
    public function modifierProfil(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user instanceof Utilisateur) {
            throw $this->createAccessDeniedException('Utilisateur non valide');
        }

        // Récupération sécurisée
        $nom = $request->request->get('nom', '');
        $prenom = $request->request->get('prenom', '');
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');

        // Vérifie que les champs requis sont présents
        if (!empty($nom) && !empty($prenom) && !empty($email)) {
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);

            if (!empty($password)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);
            }

            $em->flush();
        } else {
            // Gestion simple si formulaire vide ou invalide
            $this->addFlash('error', 'Tous les champs doivent être remplis !');
        }

        return $this->redirectToRoute('app_profil');
    }

}
