import 'package:flutter/material.dart';

class Patient {
  final int idPatient;
  final bool presence;
  final bool excuse;
  final String nomPatient;
  final String prenomPatient;
  final String mailCoordonnees;
  final bool valider;
  final Color backgroundColor;
  final bool isAllDay;

  const Patient({
    required this.idPatient,
    required this.presence,
    required this.excuse,
    required this.nomPatient,
    required this.prenomPatient,
    required this.mailCoordonnees,
    required this.valider,
    this.backgroundColor = Colors.teal,
    this.isAllDay = false,
  });

  factory Patient.fromJson(Map<String, dynamic> json) {
    return Patient(
        idPatient: json['id_patient'],
        presence: json['presence'],
        excuse: json['excuse'],
        nomPatient: json['nom_patient'],
        prenomPatient: json['prenom_patient'],
        mailCoordonnees: json['mail_coordonnees'],
        valider: json['valider']);
  }
}
