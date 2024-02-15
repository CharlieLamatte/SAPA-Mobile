import 'package:json_annotation/json_annotation.dart';

@JsonSerializable()
class MonCorps {
  String? taille;
  String? poids;
  String? imc;

  MonCorps({
    this.taille,
    this.poids,
    this.imc,
  });

  factory MonCorps.fromJson(Map<String, dynamic> json) => _$MonCorpsFromJson(json);

  Map<String, dynamic> toJson() => _$MonCorpsToJson(this);
}

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

MonCorps _$MonCorpsFromJson(Map<String, dynamic> json) => MonCorps(
  taille: json['taille'] == null
      ? null
      : json['taille'] as String,
  poids: json['poids'] == null
      ? null
      : json['poids'] as String,
  imc: json['imc'] == null
      ? null
      : json['imc'] as String,
);

Map<String, dynamic> _$MonCorpsToJson(MonCorps instance) => <String, dynamic>{
  'taille': instance.taille,
  'poids': instance.poids,
  'imc': instance.imc,
};