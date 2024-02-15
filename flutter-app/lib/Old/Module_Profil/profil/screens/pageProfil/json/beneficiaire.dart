import 'package:json_annotation/json_annotation.dart';

import 'contact.dart';
import 'infoPerso.dart';
import 'monCorps.dart';

/* class Category {
  final String label;
  final String data;

  Category(
      this.label,
      this.data
      );
} */

@JsonSerializable()
class Beneficiaire {
  InfoPerso? infoPerso;
  Contact? contact;
  MonCorps? monCorps;

  Beneficiaire({
    this.infoPerso,
    this.contact,
    this.monCorps,
  });

  factory Beneficiaire.fromJson(Map<String, dynamic> json) => Beneficiaire(
    infoPerso: json["infoPerso"],
    contact:   json["contact"],
    monCorps:  json["monCorps"],
  );

  Map<String, dynamic> toJson() =>
      {
        "infoPerso": infoPerso,
        "contact": contact,
        "monCorps": monCorps,
      };
}