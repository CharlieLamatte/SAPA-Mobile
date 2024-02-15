<?php

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    private Connexion $conn;

    public function __construct(Connexion $conn)
    {
        $this->conn = $conn;
    }

    #[Route(path:'/login', methods:['POST'])]
    public function login(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $user = $data['pseudo'];
        $password = password_hash($data['mdp'], PASSWORD_DEFAULT);

        try {
            $this->conn->connect(); // Tentative de connexion à la base de données
            $this->conn->login($user, $password); // Tentative de connexion de l'utilisateur

            // Si tout s'est bien passé, renvoyer une réponse JSON avec un statut 200
            return $this->json(['statut' => 'ok'], 200);
        } catch (Exception $e) {
            // En cas d'erreur, renvoyer une réponse JSON avec un statut approprié et un message d'erreur
            return $this->json(['statut' => 'error', 'message' => $e->getMessage()], 403);
        }
    }
}
