<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Controller\LoginController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class ParticipantController extends AbstractController
{
    #[Route('/SAPA-Mobile/Participants/GetAll', name: 'get_all_participants', methods: ['POST'])]
    public function getAllParticipants(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        $loginController = new LoginController();
        $response = $loginController->index($entityManager, $request);
        $id_seance = $data['id_seance'];
        
        if ($response->getStatusCode() === 200) {
            $participants = $entityManager->getRepository(Participant::class)->findBySeanceId($id_seance);

            $participantsArray = [];
            foreach ($participants as $participant) {
                $participantsArray[] = [
                    'id_seance' => $participant->getIdSeance(),
                    'id_patient' => $participant->getIdPatient(),
                    'presence' => $participant->isPresence(),
                    'excuse' => $participant->isExcuse(),
                    'commentaire' => $participant->getCommentaire(),
                    'id_coordonnees' => $participant->getIdCoordonnees(),
                    'nom_patient' => $participant->getNomPatient(),
                    'prenom_patient' => $participant->getPrenomPatient(),
                    'mail_coordonnees' => $participant->getMailCoordonnees(),
                    'tel_portable_patient' => $participant->getTelPortablePatient(),
                    'tel_fixe_patient' => $participant->getTelFixePatient(),
                    'date_admission' => $participant->getDateAdmission()->format('Y-m-d'),
                    'valider' => $participant->isValider(),
                    'prenom_medecin' => $participant->getPrenomMedecin(),
                    'nom_medecin' => $participant->getNomMedecin(),
                    'nom_antenne' => $participant->getNomAntenne(),
                ];
            }
            return new JsonResponse($participantsArray, Response::HTTP_OK);
        }
        else {
            return $response;
        }
    }
}
