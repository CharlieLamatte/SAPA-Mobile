<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Controller\LoginController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class SeanceController extends AbstractController
{
    #[Route('/Seances/GetAll', name: 'app_seance')]
    public function getAllSeances(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        $loginController = new LoginController();
        $response = $loginController->index($entityManager, $request);
        $id_user = $data['id_user'];

        if ($response->getStatusCode() === 200) {
            $seanceRepository = $entityManager->getRepository(Seance::class);
            $seances = $seanceRepository->findSeancesByUserId($id_user);

            $seancesArray = [];
            foreach ($seances as $seance) {
                $seanceArray = [
                    'id' => $seance->getId(),
                    'nom_creneau' => $seance->getNomCreneau(),
                    'id_type_parcours' => $seance->getIdTypeParcours(),
                    'nombre_participants' => $seance->getNombreParticipants(),
                    'type_seance' => $seance->getTypeSeance(),
                    'id_jour' => $seance->getIdJour(),
                    'nom_structure' => $seance->getNomStructure(),
                    'id_structure' => $seance->getIdStructure(),
                    'nom_coordonnees' => $seance->getNomCoordonnees(),
                    'prenom_coordonnees' => $seance->getPrenomCoordonnees(),
                    'nom_adresse' => $seance->getNomAdresse(),
                    'complement_adresse' => $seance->getComplementAdresse(),
                    'code_postal' => $seance->getCodePostal(),
                    'nom_ville' => $seance->getNomVille(),
                    'type_parcours' => $seance->getTypeParcours(),
                    'nom_jour' => $seance->getNomJour(),
                    'heure_debut' => $seance->getHeureDebut(),
                    'heure_fin' => $seance->getHeureFin(),
                    'date_seance' => $seance->getDateSeance() ? $seance->getDateSeance()->format('Y-m-d') : null,
                    'id_creneau' => $seance->getIdCreneau(),
                    'validation_seance' => $seance->isValidationSeance(),
                    'commentaire_seance' => $seance->getCommentaireSeance(),
                    'id_user' => $seance->getIdUser(),
                ];
                $seancesArray[] = $seanceArray;
            }

            return new JsonResponse($seancesArray, Response::HTTP_OK);
        }
        else {
            return $response;
        }
    }
}
