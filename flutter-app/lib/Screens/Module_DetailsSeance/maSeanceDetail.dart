import 'package:expandable/expandable.dart';
import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Components/maNavDrawer.dart';
import 'package:flutter_application_3/Screens/Components/monAppBar.dart';
import 'package:flutter_application_3/Screens/Components/monTitreDeCategorie.dart';
import 'package:flutter_application_3/Screens/Module_ListeSeance/listeSeanceCal.dart';

import '../Module_Emargement/Emargement.dart';
import 'monChampDeSeance.dart';
import 'modifChampDeSeance.dart';

bool is_Editing = false;

class MaSeance extends StatefulWidget {

  const MaSeance({Key? key, }) : super(key: key);

  @override
  State<MaSeance> createState() => _MaSeanceState();
}

class _MaSeanceState extends State<MaSeance> {


  // liste "Details de la seance"
  List<String> ipCreneau     = ["Créneau", "Tennis en salle"];
  List<String> ipStructure   = ["Structure", "Communauté Tennis"];
  List<String> ipIntervenant = ["Intervenant", "Cyrano BERGERAC"];
  List<String> ipLieu        = ["Lieu", "11 rue Tennis Poitiers 86000"];
  List<String> ipParcours    = ["Parcours", "Autre"];
  List<String> ipType        = ["Type", "Collectif"];
  List<String> ipDate        = ["Date", "07/12/2022"];
  List<String> ipDebut       = ["Debut", "10:00"];
  List<String> ipFin         = ["Fin", "12:00"];
  List<String> ipEtat        = ["Etat", "En attente d'émargement"];

  @override
  Widget build(BuildContext context) {
    // Liste "Informations personnelles"
    List<List<String>> infoSeance = [
      ipCreneau,
      ipStructure,
      ipIntervenant,
      ipLieu,
      ipParcours,
      ipType,
      ipDate,
      ipDebut,
      ipFin,
      ipEtat,
    ];

    return SafeArea(
      child: Scaffold(

        //////////////////////////////////////////////////////////////////////
        // App bar ///////////////////////////////////////////////////////////
        appBar: MonAppBar(myTitle: 'Détails de la séance'),
        drawer: const MyNavDrawer(),

        body: Padding(
          padding: const EdgeInsets.only(
              left: 25.0,
              top: 15.0,
              right: 25.0,
              bottom: 15.0
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            // étire sur tt l'écran
            children: [

              //////////////////////////////////////////////////////////////////
              // Scroll view ///////////////////////////////////////////////////
              Expanded(
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [

                      Container(
                          decoration: BoxDecoration(
                            borderRadius: const BorderRadius.all(
                                Radius.circular(100.0)
                            ),
                            color: Theme
                                .of(context)
                                .primaryColorLight,
                          ),
                          width: 115.0,
                          height: 40.0,
                          child:
                          ElevatedButton(
                            style: ButtonStyle(
                              shape: MaterialStateProperty.all<
                                  RoundedRectangleBorder>(
                                  RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(
                                        10.0),
                                  )
                              ),
                              backgroundColor: MaterialStateProperty.all<
                                  Color>(
                                  Theme
                                      .of(context)
                                      .primaryColorLight
                              ),
                            ),

                            onPressed: () {
                                Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                    builder: (context) => const ListeSeanceCal(),
                                    )
                                );
                            },
                            child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const SizedBox(
                                    width: 3.0,
                                  ),
                                  Text(
                                    'Retour',
                                    style: TextStyle(
                                      color: Theme
                                          .of(context)
                                          .primaryColor,
                                    ),
                                  ),
                                ]
                            ),
                          )
                      ),

                      ////////////////////////////////////////////////////
                      // Partie infos séance ///////////////////////
                      ExpandablePanel(
                        // Titre de la partie //////////////////////
                        header:
                        const Row(
                            children: [
                              SizedBox(
                                height: 50.0,
                              ),
                              Expanded(
                                child: MonTitreDeCategorie(
                                  myLabelTitle: "Détails de la séance",
                                ),
                              ),
                            ]
                        ),

                        // Contenue de la partie ///////////////////
                        collapsed: const SizedBox(
                          height: 0.0,
                        ),
                        expanded: Column(
                          children: [
                            const SizedBox(
                              height: 15.0,
                            ),

                            ///////////////////////////////////////////////
                            // Créneau ///////////////////////////////////
                            MonChampDeSeance(
                              myLabel: infoSeance[0][0],
                              myData: infoSeance[0][1],
                            ),

                            const SizedBox(
                              height: 15.0,
                            ),

                            //////////////////////////////////////////
                            // Structure ///////////////////////////////////
                            MonChampDeSeance(
                              myLabel: infoSeance[1][0],
                              myData: infoSeance[1][1],
                            ),

                            const SizedBox(
                              height: 15.0,
                            ),

                            //////////////////////////////////////////
                            // Intervenant //////////////////////////////////
                            MonChampDeSeance(
                              myLabel: infoSeance[2][0],
                              myData: infoSeance[2][1],
                            ),

                            const SizedBox(
                              height: 10.0,
                            ),

                            //////////////////////////////////////////
                            // Lieu ///////////////////////////////////
                            ModifChampDeSeance(
                              myLabel: infoSeance[3][0],
                              myData: infoSeance[3][1],
                              is_editabled: is_Editing,
                            ),

                            const SizedBox(
                              height: 10.0,
                            ),

                            //////////////////////////////////////////
                            // Parcours //////////////////////////////
                            MonChampDeSeance(
                              myLabel: infoSeance[4][0],
                              myData: infoSeance[4][1],
                            ),

                            const SizedBox(
                              height: 15.0,
                            ),

                            //////////////////////////////////////////
                            // Type //////////////////////////
                            MonChampDeSeance(
                              myLabel: infoSeance[5][0],
                              myData: infoSeance[5][1],
                            ),

                            const SizedBox(
                              height: 15.0,
                            ),

                            //////////////////////////////////////////
                            // Date /////////////////////////////////
                            ModifChampDeSeance(
                              myLabel: infoSeance[6][0],
                              myData: infoSeance[6][1],
                              is_editabled: is_Editing,
                            ),

                            const SizedBox(
                              height: 15.0,
                            ),

                            //////////////////////////////////////////
                            // Debut ///////////////////////////////
                            ModifChampDeSeance(
                              myLabel: infoSeance[7][0],
                              myData: infoSeance[7][1],
                              is_editabled: is_Editing,
                            ),

                            const SizedBox(
                              height: 15.0,
                            ),

                            //////////////////////////////////////////
                            // Fin ////////////////////////////
                            ModifChampDeSeance(
                              myLabel: infoSeance[8][0],
                              myData: infoSeance[8][1],
                              is_editabled: is_Editing,
                            ),

                            const SizedBox(
                              height: 15.0,
                            ),

                            //////////////////////////////////////////
                            // Etat ///////////////////////////
                            MonChampDeSeance(
                              myLabel: infoSeance[9][0],
                              myData: infoSeance[9][1],
                            ),

                            const SizedBox(
                              height: 15.0,
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(
                        height: 20.0,
                      ),

                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                              decoration: BoxDecoration(
                                borderRadius: const BorderRadius.all(
                                    Radius.circular(100.0)
                                ),
                                color: Theme
                                    .of(context)
                                    .primaryColorLight,
                              ),
                              width: 115.0,
                              height: 40.0,
                              child:
                              ElevatedButton(
                                style: ButtonStyle(
                                  shape: MaterialStateProperty.all<
                                      RoundedRectangleBorder>(
                                      RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(
                                            10.0),
                                      )
                                  ),
                                  backgroundColor: MaterialStateProperty.all<
                                      Color>(
                                      Theme
                                          .of(context)
                                          .primaryColorLight
                                  ),
                                ),

                                onPressed: () {
                                  setState(() {
                                    is_Editing = true;
                                  });
                                  ////////LAAAAAAAAAAAAAAAAAAAAAAAAAA
                                },
                                child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      InkWell(
                                        child: Icon(
                                            Icons.create_rounded,
                                            color: Theme
                                                .of(context)
                                                .primaryColor
                                        ),
                                      ),
                                      const SizedBox(
                                        width: 5.0,
                                      ),
                                      Text(
                                        'Modifier',
                                        style: TextStyle(
                                          color: Theme
                                              .of(context)
                                              .primaryColor,
                                        ),
                                      ),
                                    ]
                                ),
                              )
                          ),

                          const SizedBox(
                            width: 25.0,
                          ),

                          Container(
                              decoration: BoxDecoration(
                                borderRadius: const BorderRadius.all(
                                    Radius.circular(100.0)
                                ),
                                color: Theme
                                    .of(context)
                                    .primaryColorLight,
                              ),
                              width: 115.0,
                              height: 40.0,
                              child:
                              ElevatedButton(
                                style: ButtonStyle(
                                  shape: MaterialStateProperty.all<
                                      RoundedRectangleBorder>(
                                      RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(
                                            10.0),
                                      )
                                  ),
                                  backgroundColor: MaterialStateProperty.all<
                                      Color>(
                                      Theme
                                          .of(context)
                                          .primaryColorLight
                                  ),
                                ),

                                onPressed: () {
                                  setState(() {
                                    is_Editing = false;
                                  });
                                  ////////LAAAAAAAAAAAAAAAAAAAAAAAAAA
                                },
                                child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      InkWell(
                                        child: Icon(
                                            Icons.check,
                                            color: Theme
                                                .of(context)
                                                .primaryColor
                                        ),
                                      ),
                                      const SizedBox(
                                        width: 5.0,
                                      ),
                                      Text(
                                        'Valider',
                                        style: TextStyle(
                                          color: Theme
                                              .of(context)
                                              .primaryColor,
                                        ),
                                      ),
                                    ]
                                ),
                              )
                          ),

                          const SizedBox(
                            width: 25.0,
                          ),

                          Container(
                              decoration: BoxDecoration(
                                borderRadius: const BorderRadius.all(
                                    Radius.circular(100.0)
                                ),
                                color: Theme
                                    .of(context)
                                    .primaryColorLight,
                              ),
                              width: 115.0,
                              height: 40.0,
                              child:
                              ElevatedButton(
                                style: ButtonStyle(
                                  shape: MaterialStateProperty.all<
                                      RoundedRectangleBorder>(
                                      RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(
                                            10.0),
                                      )
                                  ),
                                  backgroundColor: MaterialStateProperty.all<
                                      Color>(
                                      Theme
                                          .of(context)
                                          .primaryColorLight
                                  ),
                                ),

                                onPressed: () {
                                  setState(() {
                                    is_Editing = false;
                                  });
                                  ////////LAAAAAAAAAAAAAAAAAAAAAAAAAA
                                },
                                child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      InkWell(
                                        child: Icon(
                                            Icons.close,
                                            color: Theme
                                                .of(context)
                                                .primaryColor
                                        ),
                                      ),
                                      const SizedBox(
                                        width: 5.0,
                                      ),
                                      Text(
                                        'Annuler',
                                        style: TextStyle(
                                          color: Theme
                                              .of(context)
                                              .primaryColor,
                                        ),
                                      ),
                                    ]
                                ),

                              )
                          ),

                        ],
                      ),

                      const SizedBox(
                        height: 20.0,
                      ),

                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                              decoration: BoxDecoration(
                                borderRadius: const BorderRadius.all(
                                    Radius.circular(100.0)
                                ),
                                color: Theme
                                    .of(context)
                                    .primaryColorLight,
                              ),
                              width: 115.0,
                              height: 40.0,
                              child:
                              ElevatedButton(
                                style: ButtonStyle(
                                  shape: MaterialStateProperty.all<
                                      RoundedRectangleBorder>(
                                      RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(
                                            10.0),
                                      )
                                  ),
                                  backgroundColor: MaterialStateProperty.all<
                                      Color>(
                                      Theme
                                          .of(context)
                                          .primaryColorLight
                                  ),
                                ),

                                onPressed: () {
                                  setState(() {
                                    Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (context) => const Emargement(),
                                      ),
                                    );

                                  });
                                },
                                child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      const SizedBox(
                                        width: 5.0,
                                      ),
                                      Text(
                                        'Émarger',
                                        style: TextStyle(
                                          color: Theme
                                              .of(context)
                                              .primaryColor,
                                        ),
                                      ),
                                    ]
                                ),
                              )
                          ),


                          // Bouton de modification des infos sur "Ma séance"


                          const SizedBox(
                            height: 15.0,
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
