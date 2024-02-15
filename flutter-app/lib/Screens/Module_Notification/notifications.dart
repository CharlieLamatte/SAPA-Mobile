import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Components/maNavDrawer.dart';
import 'package:flutter_application_3/Screens/Components/monAppBar.dart';
import 'monChampNotif.dart';
import 'listeNotif.dart';

class Notifications extends StatefulWidget {
  const Notifications({
    Key? key
  }) : super(key: key);

  @override
  _NotificationsState createState() => _NotificationsState();
}

class _NotificationsState extends State<Notifications> {


  List<listeNotif> lesNotifs=[
    const listeNotif(
        dateNotif: "18/01/2023",
        typeSeance: "Séance tennis de table",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée"),
    const listeNotif(
        dateNotif: "19/01/2023",
        typeSeance: "Séance Gym ",
        commentaireNotif: "Rubeus, Albus, Sirius, Bellatrix, Severus, Minerva, Dolores, Pomona, Gilderoy, Remus, Quirinus, Aurora, Horace, Sibylle ne font plus parti des bénéficiaires pour cette séance. "),
    const listeNotif(
        dateNotif: "20/01/2023",
        typeSeance: "Séance Rugby",
        commentaireNotif: "Votre séance d'aujourd'hui est annulée")
  ];





  @override
  Widget build(BuildContext context) {


    return
      SafeArea(
        child: Scaffold(
          // App bar ///////////////////////////////////////////////////////////
          appBar: MonAppBar(
            myTitle: "Notifications",
          ),
          drawer: const MyNavDrawer(),

          body: Padding(
            padding: const EdgeInsets.only(
                left: 25.0,
                top: 15.0,
                right: 25.0,
                bottom: 15.0
            ),
            child: ListView.builder(
                itemCount: lesNotifs.length,
                itemBuilder: (BuildContext context, int index) {
                  return ListTile(
                    title: MonChampNotif(
                      myLabel: "${lesNotifs[index].dateNotif} - ${lesNotifs[index].typeSeance}",
                      myData: lesNotifs[index].commentaireNotif,
                    ),
                  );
                }
            )



        ),
      ));
  }
}



/*

Column(
              crossAxisAlignment: CrossAxisAlignment.stretch, // étire sur tt l'écran
              children: [

                //////////////////////////////////////////////////////////////////
                // Titre /////////////////////////////////////////////////////////
                // TODO : titre

                //////////////////////////////////////////////////////////////////
                // Scroll view ///////////////////////////////////////////////////
                Expanded(
                  child: SingleChildScrollView(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [

                        const SizedBox(
                          height: 15.0,
                        ),
                        Expanded(
                          child:ListView.builder(
                              itemCount: mesTrucs.length,
                              itemBuilder: (context, index) {
                                return const ListTile(
                                  title: Text("bonjour"),
                                );
                              }
                          )

                        ),
                    ],
                  ),
                ),

                //const SizedBox(
                //  height: 15.0,
                //),

                /////////////////////////////////////////////
                // Bouton ///////////////////////////////////
                // TODO : bouton
        ),
  ],
          ),
 */