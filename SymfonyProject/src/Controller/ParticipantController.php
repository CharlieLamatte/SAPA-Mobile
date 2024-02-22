<?php

namespace App\Controller;

use App\Entity\Participant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class ParticipantController extends AbstractController
{
    #[Route('/Participants/GetAll/{id_seance}', name: 'app_participant')]
    public function getAllParticipants(EntityManagerInterface $entityManager, $id_seance): JsonResponse
    {
        
            $participants = $entityManager->getRepository(Participant::class)->findBySeanceId($id_seance);

            $participantsArray = [];
            foreach ($participants as $participant) {
                $participantsArray[] = [
                    'id' => $participant->getId(),
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

            return $this->json($participantsArray);
    }
}