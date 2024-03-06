class Seance {
  int id;
  String nomCreneau;
  int idTypeParcours;
  int nombreParticipants;
  String typeSeance;
  int idJour;
  String nomStructure;
  int idStructure;
  String nomCoordonnees;
  String prenomCoordonnees;
  String nomAdresse;
  String complementAdresse;
  String codePostal;
  String nomVille;
  String typeParcours;
  String nomJour;
  String heureDebut;
  String heureFin;
  String dateSeance;
  int idCreneau;
  bool validationSeance;
  String commentaireSeance;
  int idUser;

  Seance({
    required this.id,
    required this.nomCreneau,
    required this.idTypeParcours,
    required this.nombreParticipants,
    required this.typeSeance,
    required this.idJour,
    required this.nomStructure,
    required this.idStructure,
    required this.nomCoordonnees,
    required this.prenomCoordonnees,
    required this.nomAdresse,
    required this.complementAdresse,
    required this.codePostal,
    required this.nomVille,
    required this.typeParcours,
    required this.nomJour,
    required this.heureDebut,
    required this.heureFin,
    required this.dateSeance,
    required this.idCreneau,
    required this.validationSeance,
    required this.commentaireSeance,
    required this.idUser,
  });

  factory Seance.fromJson(Map<String, dynamic> json) {
    return Seance(
      id: json['id'],
      nomCreneau: json['nom_creneau'],
      idTypeParcours: json['id_type_parcours'],
      nombreParticipants: json['nombre_participants'],
      typeSeance: json['type_seance'],
      idJour: json['id_jour'],
      nomStructure: json['nom_structure'],
      idStructure: json['id_structure'],
      nomCoordonnees: json['nom_coordonnees'],
      prenomCoordonnees: json['prenom_coordonnees'],
      nomAdresse: json['nom_adresse'],
      complementAdresse: json['complement_adresse'],
      codePostal: json['code_postal'],
      nomVille: json['nom_ville'],
      typeParcours: json['type_parcours'],
      nomJour: json['nom_jour'],
      heureDebut: json['heure_debut'],
      heureFin: json['heure_fin'],
      dateSeance: json['date_seance'],
      idCreneau: json['id_creneau'],
      validationSeance: json['validation_seance'],
      commentaireSeance: json['commentaire_seance'],
      idUser: json['id_user'],
    );
  }
}
