<?php

namespace App\Controller;

use App\Entity\ARole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;

class LoginController extends AbstractController
{
    #[Route('/login/{username}/{password}', name: 'app_login')]
    public function index(EntityManagerInterface $entityManager, $username, $password)
    {
        $user = $entityManager->getRepository(Users::class)->findOneBy(['identifiant'=> $username]);
        
        if (!$user) {
            throw $this->createNotFoundException('Mot de passe ou email invalide.');
        }
        else {
            if (!$this->is_password_valid($user, $password)) {
                return new JsonResponse(['error' => 'Mot de passe ou email invalide.'], Response::HTTP_UNAUTHORIZED);
            }
            
            if ($this->is_user_deactivated($user)) {
                return new JsonResponse(['error' => 'Le compte a été désactivé.'], Response::HTTP_FORBIDDEN);
            }
            if (!$this->is_intervenant($entityManager, $user)) {
                return new JsonResponse(['error' => 'Le compte n\'est pas lié à un intervenant.'], Response::HTTP_FORBIDDEN);
            }
            $this->update_compteur($user);
            return new JsonResponse(['success' => true]);
        }
    }

    public function is_password_valid(Users $user, string $password): bool{
        return password_verify($password, $user->getPswd());
    }    

    public function is_user_deactivated(Users $user): bool{
        if($user->isIsDeactivated() != null){
            return true;
        }
        else {
            return false;
        }
    }

    public function is_intervenant(EntityManagerInterface $entityManager, Users $user): bool{
        $roles = $entityManager->getRepository(ARole::class)->findBy(['id_user'=> $user->getId()]);
        $is_intervenant = false;
        foreach ($roles as $role) {
            if ($role->getIdRoleUser() === 3) {
                $is_intervenant = true;
            }
        }
        return $is_intervenant;
    }

    public function update_compteur(Users $user) {
        $compteur = $user->getCompteur();
        if ($compteur !== null) {
            $user->setCompteur($compteur + 1);
        }
        else {
            $user->setCompteur(1);
        }
    }
}

// 1. Pour chacune de tes identités/vue ==> Faut faire un controller
// 2. Chaque table en BD ==> Faire une entité  ==> php bin/console make:entity Users 
// 2 bis ==> Par entité créée, Repository ==> avec méthode par défaut ==> remettre de l'ordre 
