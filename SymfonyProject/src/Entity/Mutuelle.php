<?php

namespace Sportsante86\Sapa\Model;

use Exception;
 ;

use Sportsante86\Sapa\Outils\ChaineCharactere;

class Mutuelle
{
    private PDO $pdo;
    private string $errorMessage = '';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return string the error message of the last operation
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Creates a mutuelle
     *
     * required parameters:
     * [
     *     'nom' => string,
     *     'code_postal' => string,
     *     'nom_ville' => string,
     * ]
     *
     * optional parameters:
     * [
     *     'nom_adresse' => string,
     *     'complement_adresse' => string,
     *     'email' => string,
     *     'tel_portable' => string,
     *     'tel_fixe' => string,
     * ]
     *
     * @param array $parameters
     * @return false|string the id of the medecin or false on failure
     */
    public function create(array $parameters)
    {
        if (empty($parameters['nom']) ||
            empty($parameters['code_postal']) ||
            empty($parameters['nom_ville'])) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // paramètres obligatoires
            $nom = mb_strtoupper(trim($parameters['nom']), 'UTF-8');
            $nom_ville = trim($parameters['nom_ville']);
            $code_postal = $parameters['code_postal'];

            // paramètres optionnels
            $email = isset($parameters['email']) ?
                trim($parameters['email']) :
                "";
            $tel_fixe = isset($parameters['tel_fixe']) ?
                filter_var(
                    $parameters['tel_fixe'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";
            $tel_portable = isset($parameters['tel_portable']) ?
                filter_var(
                    $parameters['tel_portable'],
                    FILTER_SANITIZE_NUMBER_INT,
                    ['options' => ['default' => ""]]
                ) :
                "";
            $nom_adresse = isset($parameters['nom_adresse']) ?
                ChaineCharactere::mb_ucfirst(trim($parameters['nom_adresse'])) :
                "";
            $complement_adresse = isset($parameters['complement_adresse']) ?
                trim($parameters['complement_adresse']) :
                "";

            ////////////////////////////////////////////////////
            // Vérification que la mutuelle n'existe pas déja
            ////////////////////////////////////////////////////
            $query = '
                SELECT nom_coordonnees, nom_ville, code_postal
                FROM mutuelles
                         JOIN coordonnees c on c.id_coordonnees = mutuelles.id_coordonnees
                         JOIN se_trouve st on mutuelles.id_mutuelle = st.id_mutuelle
                         JOIN se_localise_a sla on st.id_adresse = sla.id_adresse
                         JOIN villes v on sla.id_ville = v.id_ville
                WHERE c.nom_coordonnees LIKE :nom
                  AND v.nom_ville LIKE :nom_ville
                  AND v.code_postal = :code_postal
                LIMIT 1';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom', $nom);
            $stmt->bindValue(':nom_ville', $nom_ville);
            $stmt->bindValue(':code_postal', $code_postal);

            if (!$stmt->execute()) {
                throw new Exception('Error vérification mutuelle');
            }
            if ($stmt->rowCount() > 0) {
                throw new Exception('Error: la mutuelle ' . $nom . ' existe déjà dans la ville ' . $nom_ville);
            }

            ////////////////////////////////////////////////////
            // INSERT dans coordonnées
            ////////////////////////////////////////////////////
            $query = '
                INSERT INTO coordonnees
                (nom_coordonnees, tel_fixe_coordonnees, tel_portable_coordonnees, mail_coordonnees)
                VALUES (:nom, :tel_fixe, :tel_portable, :mail)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom', $nom);
            $stmt->bindValue(':tel_fixe', $tel_fixe);
            $stmt->bindValue(':tel_portable', $tel_portable);
            $stmt->bindValue(':mail', $email);

            if (!$stmt->execute()) {
                throw new Exception('Error insert coordonnees');
            }
            $id_coordonnees = $this->pdo->lastInsertId();

            ////////////////////////////////////////////////////
            // INSERT dans mutuelles
            ////////////////////////////////////////////////////
            $query = '
                INSERT INTO mutuelles
                    (id_coordonnees)
                VALUES (:id_coordonnees)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_coordonnees', $id_coordonnees);

            if (!$stmt->execute()) {
                throw new Exception('Error insert mutuelles' . $id_coordonnees);
            }
            $id_mutuelle = $this->pdo->lastInsertId();

            ////////////////////////////////////////////////////
            // UPDATE de la mutuelle dans coordonnées
            ////////////////////////////////////////////////////
            $query = '
                UPDATE coordonnees
                SET id_mutuelle = :id_mutuelle
                WHERE id_coordonnees = :id_coordonnees';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_coordonnees', $id_coordonnees);
            $stmt->bindValue(':id_mutuelle', $id_mutuelle);

            if (!$stmt->execute()) {
                throw new Exception('Error UPDATE coordonnees');
            }

            ////////////////////////////////////////////////////
            // Insertion dans adresse
            ////////////////////////////////////////////////////
            $query = '
                INSERT INTO adresse(nom_adresse, complement_adresse)
                VALUES (:nom_adresse, :complement_adresse)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_adresse', $nom_adresse);
            $stmt->bindValue(':complement_adresse', $complement_adresse);

            if (!$stmt->execute()) {
                throw new Exception('Error insert adresse');
            }
            $id_adresse = $this->pdo->lastInsertId();

            ////////////////////////////////////////////////////
            // Récupération de l'id de la ville -> insertion dans se_localise_a
            ////////////////////////////////////////////////////
            $query = '
                SELECT id_ville
                from villes
                WHERE nom_ville LIKE :nom_ville
                  AND code_postal = :code_postal';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':nom_ville', $nom_ville);
            $stmt->bindValue(':code_postal', $code_postal);

            if (!$stmt->execute()) {
                throw new Exception('Error select villes');
            }
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_ville = $data['id_ville'] ?? null;
            if (empty($id_ville)) {
                throw new Exception(
                    'Error: La ville \'' . $nom_ville . '\' qui a en code_postal \'' . $code_postal . '\' n\'a pas été trouvé dans la BDD'
                );
            }

            ////////////////////////////////////////////////////
            // Insertion dans se_localise_a
            ////////////////////////////////////////////////////
            $query = '
                INSERT INTO se_localise_a(id_adresse, id_ville)
                VALUES (:id_adresse, :id_ville)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_adresse', $id_adresse);
            $stmt->bindValue(':id_ville', $id_ville);

            if (!$stmt->execute()) {
                throw new Exception('Error insert se_localise_a');
            }

            ////////////////////////////////////////////////////
            // Insertion dans se_trouve
            ////////////////////////////////////////////////////
            $query = '
                INSERT INTO se_trouve(id_adresse, id_mutuelle)
                VALUES (:id_adresse, :id_mutuelle)';
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':id_adresse', $id_adresse);
            $stmt->bindValue(':id_mutuelle', $id_mutuelle);

            if (!$stmt->execute()) {
                throw new Exception('Error insert se_trouve');
            }

            $this->pdo->commit();
            return $id_mutuelle;
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * @param $id_mutuelle
     * @return false|array Returns an associative array or false on failure
     */
    public function readOne($id_mutuelle)
    {
        if (empty($id_mutuelle)) {
            $this->errorMessage = "Il manque au moins un paramètre obligatoire";
            return false;
        }

        $query = '
            SELECT mutuelles.id_mutuelle,
                   nom_coordonnees          as nom,
                   nom_ville,
                   code_postal,
                   tel_fixe_coordonnees     as tel_fixe,
                   tel_portable_coordonnees as tel_portable,
                   mail_coordonnees         as email,
                   nom_adresse,
                   complement_adresse
            FROM mutuelles
                     JOIN coordonnees c on c.id_coordonnees = mutuelles.id_coordonnees
                     JOIN se_trouve st on mutuelles.id_mutuelle = st.id_mutuelle
                     JOIN se_localise_a sla on st.id_adresse = sla.id_adresse
                     JOIN adresse a on sla.id_adresse = a.id_adresse
                     JOIN villes v on sla.id_ville = v.id_ville
            WHERE mutuelles.id_mutuelle = :id_mutuelle';
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id_mutuelle', $id_mutuelle);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return false|array Returns an array
     */
    public function readAll()
    {
        $query = '
            SELECT mutuelles.id_mutuelle,
                   nom_coordonnees          as nom,
                   nom_ville,
                   code_postal,
                   tel_fixe_coordonnees     as tel_fixe,
                   tel_portable_coordonnees as tel_portable,
                   mail_coordonnees         as email,
                   nom_adresse,
                   complement_adresse
            FROM mutuelles
                     JOIN coordonnees c on c.id_coordonnees = mutuelles.id_coordonnees
                     JOIN se_trouve st on mutuelles.id_mutuelle = st.id_mutuelle
                     JOIN se_localise_a sla on st.id_adresse = sla.id_adresse
                     JOIN adresse a on sla.id_adresse = a.id_adresse
                     JOIN villes v on sla.id_ville = v.id_ville
            ORDER BY nom';
        $stmt = $this->pdo->prepare($query);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}