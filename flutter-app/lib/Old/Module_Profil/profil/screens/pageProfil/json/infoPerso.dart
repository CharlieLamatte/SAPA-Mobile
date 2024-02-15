import 'package:json_annotation/json_annotation.dart';

@JsonSerializable()
class InfoPerso {
  String? prenom;
  String? nom;
  String? sexe;
  String? ddn;
  String? telFixe;
  String? telPort;
  String? email;
  String? adresse;
  String? complement;
  String? cp;
  String? ville;

  InfoPerso({
    this.prenom,
    this.nom,
    this.sexe,
    this.ddn,
    this.telFixe,
    this.telPort,
    this.email,
    this.adresse,
    this.complement,
    this.cp,
    this.ville,
  });

  factory InfoPerso.fromJson(Map<String, dynamic> json) => _$InfoPersoFromJson(json);

  Map<String, dynamic> toJson() => _$InfoPersoToJson(this);
}

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

InfoPerso _$InfoPersoFromJson(Map<String, dynamic> json) => InfoPerso(
  prenom: json['prenom'] == null
      ? null
      : json['prenom'] as String,
  nom: json['nom'] == null
      ? null
      : json['nom'] as String,
  sexe: json["sexe"] == null
      ? null
      : json['sexe'] as String,
  ddn: json["ddn"] == null
      ? null
      : json['ddn'] as String,
  telFixe: json['telFixe'] == null
      ? null
      : json['telFixe'] as String,
  telPort: json['telPort'] == null
      ? null
      : json['telPort'] as String,
  email: json["email"] == null
      ? null
      : json['email'] as String,
  adresse:    json["adresse"] == null
      ? null
      : json['adresse'] as String,
  complement: json["complement"] == null
      ? null
      : json['complement'] as String,
  cp: json["cp"] == null
      ? null
      : json['cp'] as String,
  ville: json["ville"] == null
      ? null
      : json['ville'] as String,
);

Map<String, dynamic> _$InfoPersoToJson(InfoPerso instance) => <String, dynamic>{
  'prenom': instance.prenom,
  'nom': instance.nom,
  "sexe": instance.sexe,
  "ddn": instance.ddn,
  'telFixe': instance.telFixe,
  'telPort': instance.telPort,
  "email": instance.email,
  "adresse": instance.adresse,
  "complement": instance.complement,
  "cp": instance.cp,
  "ville": instance.ville,
};