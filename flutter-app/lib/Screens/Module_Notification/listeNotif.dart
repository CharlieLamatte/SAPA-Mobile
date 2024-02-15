import 'package:flutter/material.dart';

class listeNotif extends StatefulWidget {
  final String dateNotif, typeSeance, commentaireNotif;
  final double? space;

  const listeNotif({
    Key? key,
    required this.dateNotif,
    required this.typeSeance,
    required this.commentaireNotif,
    this.space
  }) : super(key: key);

  @override
  State<StatefulWidget> createState() {
    // TODO: implement createState
    throw UnimplementedError();
  }

}