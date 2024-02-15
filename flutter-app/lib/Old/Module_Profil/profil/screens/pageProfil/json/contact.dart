import 'package:json_annotation/json_annotation.dart';

@JsonSerializable()
class Contact {
  String? prenom;
  String? nom;
  String? telFixe;
  String? telPort;

  Contact({
    this.prenom,
    this.nom,
    this.telFixe,
    this.telPort,
  });

  factory Contact.fromJson(Map<String, dynamic> json) => _$ContactFromJson(json);

  Map<String, dynamic> toJson() => _$ContactToJson(this);
}

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

Contact _$ContactFromJson(Map<String, dynamic> json) => Contact(
  prenom: json['prenom'] == null
      ? null
      : json['prenom'] as String,
  nom: json['nom'] == null
      ? null
      : json['nom'] as String,
  telFixe: json['telFixe'] == null
      ? null
      : json['telFixe'] as String,
  telPort: json['telPort'] == null
      ? null
      : json['telPort'] as String,
);

Map<String, dynamic> _$ContactToJson(Contact instance) => <String, dynamic>{
  'prenom': instance.prenom,
  'nom': instance.nom,
  'telFixe': instance.telFixe,
  'telPort': instance.telPort,
};