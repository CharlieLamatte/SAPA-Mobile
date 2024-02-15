import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Components/maNavDrawer.dart';
import 'package:flutter_application_3/Screens/Components/monAppBar.dart';

import 'Accueil_Components/myCategoryGrid.dart';
import 'Accueil_Components/myHomeReminder.dart';
import 'Accueil_Components/myMotivationCarrousel.dart';

class MyHomePage extends StatefulWidget {
  const MyHomePage({
    Key? key,
  }) : super(key: key);

  @override
  State<StatefulWidget> createState() => MyHomePageState();
}

class MyHomePageState extends State<MyHomePage> {
  String prenom = "Anonyme";

  @override
  Widget build(BuildContext context) {
    return
      SafeArea(
        child: Scaffold(
          appBar: MonAppBar(
            myTitle: 'Bienvenue $prenom',
          ),
          drawer: const MyNavDrawer(),

          body: const SingleChildScrollView(
            child: Column(
                children: [
                  Padding(
                    padding: EdgeInsets.only(
                      left: 20.0,
                      top: 20.0,
                      right: 25.0,
                      bottom: 15.0,
                    ),
                    child : Column(
                      children: [
                        MyHomeReminder(),
                        SizedBox(height: 15.0),
                        MyCategoryGrid(),
                      ],
                    ),
                  ),
                  MyMotivationCarrousel(),
                ]
            ),
          ),
        ),
      );
  }
}
