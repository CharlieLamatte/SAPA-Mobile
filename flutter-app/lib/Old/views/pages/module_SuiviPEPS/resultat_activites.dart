import 'package:flutter_application_3/old/views/components/barFiltre.dart';
import 'package:flutter_application_3/old/views/components/navigationBar.dart';
import 'package:flutter/material.dart';

import 'designActivite.dart';

class afficheResultats extends StatefulWidget {
  const afficheResultats({Key? key}) : super(key: key);


  @override
  State<afficheResultats> createState() => _afficheResultatsState();
}

class _afficheResultatsState extends State<afficheResultats> {
  @override
  Widget build(BuildContext context) {
    return SafeArea(
        child: Scaffold(
            ///////////////////////////////////////////////// AppBar
            appBar: AppBar(
                titleSpacing: 0.0,
                elevation: 0,
                backgroundColor: Theme.of(context).scaffoldBackgroundColor,
                leading: IconButton(
                  icon: const Icon(Icons.menu_rounded),
                  color: Colors.teal,
                  onPressed: () => {}, // TODO
                ),
                actions: [
                  IconButton(
                    icon: const Icon(Icons.power_settings_new_rounded),
                    color: Colors.teal,
                    onPressed: () => {}, // TODO
                  ),
                ]
            ),
            
            // Scroll view ///////////////////////////////////////////////////////
            body: const Center(
              child: SingleChildScrollView(
                padding: EdgeInsets.fromLTRB(15.0, 0.0, 15.0, 20.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Resultats()
                  ],
                ),
              )
            ),
            
            bottomNavigationBar: const navigationBar(),
          )
      );
  }
}

///////////////////////////////////////////////// Mise en place du formulaire
class Resultats extends StatefulWidget {
  const Resultats({Key? key}) : super(key: key);

  @override
  _ResultatsState createState() => _ResultatsState();
}

class _ResultatsState extends State<Resultats> {


  final List ActiviteList = [
    {
      'title': 'Activité 1',
      'place': 'Lieu 1',
    },
    {
      'title': 'Activité 2',
      'place': 'Lieu 2',
    },
    {
      'title': 'Activité 3',
      'place': 'Lieu 3',
    },
    {
      'title': 'Activié  4',
      'place': 'Lieu 4',
    },
        {
      'title': 'Activité 5',
      'place': 'Lieu 5',
    },
    {
      'title': 'Activité 6',
      'place': 'Lieu 6',
    },
    {
      'title': 'Activité 7',
      'place': 'Lieu 7',
    },
    {
      'title': 'Activié  8',
      'place': 'Lieu 8',
    },
  ];
  @override
  Widget build(BuildContext context) {
    return Container(
      child: Column(
        children: [
          const filtreBar(),
           Column(
            children: ActiviteList.map((activite) {
              return ActiviteCard(activite);
            }).toList(), 
          ), 
        ],
      ),
    );
  }
}
  


