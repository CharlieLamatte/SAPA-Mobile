<?php

namespace App\Controller;

use App\Entity\APartcipeA;
use App\Entity\Participant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class ParticipantController extends AbstractController
{
    #[Route('/Participants/GetAll/{id_seance}', name: 'get_all_participants')]
    public function getAllParticipants(EntityManagerInterface $entityManager, $id_seance): JsonResponse
    {
        //Implémenter l'authentification avant d'exécuter l'appel
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

        return $this->json($participantsArray);
    }

    #[Route('/Participants/Emarger', name: 'set_emargement_seance')]
    public function setEmargementSeance(Request $request, EntityManagerInterface $entityManager) {
        $jsonData = json_decode($request->getContent(), true);
        if (!isset($jsonData['id_seance']) || !isset($jsonData['emargements']) || !is_array($jsonData['emargements'])) {
            return new JsonResponse(['message' => 'Invalid JSON data'], 400);
        }

        $id_seance = $jsonData['id_seance'];
        $emargements = $jsonData['emargements'];

        try {
            foreach ($emargements as $emargement) {
                $oldEmargement = $entityManager->getRepository(APartcipeA::class)->findOneBy(['id_seance'=>$id_seance, 'id_patient'=>$emargement['id_patient']]);
                if ($oldEmargement === null) {
                    throw $this->createNotFoundException('No product found for id_seance '.$id_seance.' or id_patient '.$emargement['id_patient'].'.');
                }
                $entityManager->remove($oldEmargement);
                $entityManager->flush();

                $excuse = $emargement['present'] == "1" ? null : $emargement['excuse'];
                $newEmargement = new APartcipeA();
                $newEmargement->setIdPatient($emargement['id_patient']);;
                $newEmargement->setIdSeance($id_seance);
                $newEmargement->setPresence($emargement['present']);
                if ($excuse === null) {
                    $newEmargement->setExcuse(null);
                } else {
                    $newEmargement->setExcuse($excuse);
                }
                $newEmargement->setCommentaire($emargement['commentaire']);
                $entityManager->persist($newEmargement);
                $entityManager->flush();
            }

            return new JsonResponse(['message' => 'Emargements successfully updated'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error INSERT INTO a_participe_a'], 500);
        }
    }
}
