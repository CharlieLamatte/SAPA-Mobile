<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\USERS;
use Symfony\Component\Serializer\SerializerInterface;

class LoginController extends AbstractController
{
    #[Route('/login/{username}/{password}', name: 'app_login')]
    public function index(EntityManagerInterface $entityManager, $username, $password)
    {
        $user = $entityManager->getRepository(USERS::class)->findOneBy(['username'=> $username]);
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
            //if (!$this->init_session($user)) {
            //    throw $this->createNotFoundException('Error init_session');
            //}
            //$this->update_compteur($_SESSION['id_user']);
        return true;
        }
    }

    public function is_password_valid(USERS $user, string $password): bool{
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
}


// 1. Pour chacune de tes identités/vue ==> Faut faire un controller
// 2. Chaque table en BD ==> Faire une entité  ==> php bin/console make:entity Users 
// 2 bis ==> Par entité créée, Repository ==> avec méthode par défaut ==> remettre de l'ordre 
