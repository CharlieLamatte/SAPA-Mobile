<?php

namespace App\Controller;

use App\Entity\APartcipeA;
use App\Controller\LoginController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class AParticipeAController extends AbstractController
{
    #[Route('/SAPA-Mobile/EmargerSeance', name: 'set_emargement_seance', methods: ['POST'])]
    public function setEmargementSeance(Request $request, EntityManagerInterface $entityManager) {
        $content = $request->getContent();
        $data = json_decode($content, true);

        $loginController = new LoginController();
        $response = $loginController->index($entityManager, $request);

        $id_seance = $data['id_seance'];
        $emargements = $data['emargements'];

        if ($response->getStatusCode() === 200) {
            try {
                foreach ($emargements as $emargement) {
                    $deleteResponse = $entityManager->getRepository(APartcipeA::class)->deleteBySeanceAndPatientId($id_seance, $emargement['id_patient']);
                    print($deleteResponse->getContent());

                    $excuse = $emargement['present'] == 1 ? null : $emargement['excuse'];
                    $newEmargement = new APartcipeA();
                    $newEmargement->setIdPatient($emargement['id_patient']);;
                    $newEmargement->setIdSeance($id_seance);
                    $newEmargement->setPresence($emargement['present']);
                    if ($excuse === null) {
                        $newEmargement->setExcuse(null);
                    } else {
                        $newEmargement->setExcuse($excuse);
                    }
                    if ($emargement['commentaire'] !== null) {
                        $newEmargement->setCommentaire($emargement['commentaire']);
                    }
                    $createResponse = $entityManager->getRepository(APartcipeA::class)->createEmargement($newEmargement);
                    print ($createResponse->getContent());
                }
                return new JsonResponse(['message' => 'Emargements successfully updated'], 200);
            } catch (\Exception $e) {
                return new JsonResponse(['message' => 'Error INSERT INTO a_participe_a: '.$e->getMessage()], 500);
            }
        } else {
            return $response;
        }
    }
}
