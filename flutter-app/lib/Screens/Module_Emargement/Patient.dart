import 'package:flutter/material.dart';

class Patient {
  final int idSeance;
  final int idPatient;
  final bool? presence;
  final bool? excuse;
  final String? commentaire;
  final int? idCoordonnees;
  final String nomPatient;
  final String prenomPatient;
  final String? mailCoordonnees;
  final String? telPortablePatient;
  final String? telFixePatient;
  final String? dateAdmission;
  final bool? valider;
  final String? prenomMedecin;
  final String? nomMedecin;
  final String nomAntenne;
  final Color backgroundColor;
  final bool isAllDay;

  const Patient({
    required this.idSeance,
    required this.idPatient,
    this.presence,
    this.excuse,
    this.commentaire,
    this.idCoordonnees,
    required this.nomPatient,
    required this.prenomPatient,
    this.mailCoordonnees,
    this.telPortablePatient,
    this.telFixePatient,
    this.dateAdmission,
    this.valider,
    this.prenomMedecin,
    this.nomMedecin,
    required this.nomAntenne,
    this.backgroundColor = Colors.teal,
    this.isAllDay = false,
  });

  factory Patient.fromJson(Map<String, dynamic> json) {
    return Patient(
      idSeance: json['id_seance'],
      idPatient: json['id_patient'],
      presence: json['presence'],
      excuse: json['excuse'],
      commentaire: json['commentaire'],
      idCoordonnees: json['id_coordonnees'],
      nomPatient: json['nom_patient'],
      prenomPatient: json['prenom_patient'],
      mailCoordonnees: json['mail_coordonnees'],
      telPortablePatient: json['tel_portable_patient'],
      telFixePatient: json['tel_fixe_patient'],
      dateAdmission: json['date_admission'],
      valider: json['valider'],
      prenomMedecin: json['prenom_medecin'],
      nomMedecin: json['nom_medecin'],
      nomAntenne: json['nom_antenne'],
    );
  }
}
