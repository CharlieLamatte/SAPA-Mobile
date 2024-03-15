import 'package:expandable/expandable.dart';
import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Components/maNavDrawer.dart';
import 'package:flutter_application_3/Screens/Components/monAppBar.dart';
import 'package:flutter_application_3/Screens/Components/monTitreDeCategorie.dart';
import 'package:flutter_application_3/Screens/Module_ListeSeance/ListeSeance_Utils/evenement.dart';
import 'package:flutter_application_3/Screens/Module_ListeSeance/listeSeanceCal.dart';

import '../Module_Emargement/Emargement.dart';
import 'monChampDeSeance.dart';
import 'modifChampDeSeance.dart';

bool is_Editing = false;

class MaSeance extends StatefulWidget {
  final Evenement seance;
  const MaSeance({
    Key? key,
    required this.seance,
  }) : super(key: key);

  @override
  State<MaSeance> createState() => _MaSeanceState();
}

class _MaSeanceState extends State<MaSeance> {
  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Scaffold(
        //////////////////////////////////////////////////////////////////////
        // App bar ///////////////////////////////////////////////////////////
        appBar: MonAppBar(myTitle: 'Détails de la séance'),
        drawer: const MyNavDrawer(),

        body: Padding(
          padding: const EdgeInsets.only(
              left: 25.0, top: 15.0, right: 25.0, bottom: 15.0),
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
                            borderRadius:
                                const BorderRadius.all(Radius.circular(100.0)),
                            color: Theme.of(context).primaryColorLight,
                          ),
                          width: 115.0,
                          height: 40.0,
                          child: ElevatedButton(
                            style: ButtonStyle(
                              shape: MaterialStateProperty.all<
                                      RoundedRectangleBorder>(
                                  RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(10.0),
                              )),
                              backgroundColor: MaterialStateProperty.all<Color>(
                                  Theme.of(context).primaryColorLight),
                            ),
                            onPressed: () {
                              Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) =>
                                        const ListeSeanceCal(),
                                  ));
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
                                      color: Theme.of(context).primaryColor,
                                    ),
                                  ),
                                ]),
                          )),

                      ////////////////////////////////////////////////////
                      // Partie infos séance ///////////////////////
                      ExpandablePanel(
                        // Titre de la partie //////////////////////
                        header: const Row(children: [
                          SizedBox(
                            height: 50.0,
                          ),
                          Expanded(
                            child: MonTitreDeCategorie(
                              myLabelTitle: "Détails de la séance",
                            ),
                          ),
                        ]),

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
                              myLabel: "Créneau",
                              myData: widget.seance.nomCreneau ?? "",
                            ),

                            const SizedBox(
                              height: 15.0,
                            ),

                            //////////////////////////////////////////
                            // Structure ///////////////////////////////////
                            MonChampDeSeance(
                              myLabel: "Structure",
                              myData: widget.seance.nomStructure ?? "",
                            ),

                            const SizedBox(
                              height: 15.0,
                            ),

                            //////////////////////////////////////////
                            // Intervenant //////////////////////////////////
                            MonChampDeSeance(
                              myLabel: "Intervenant",
                              myData:
                                  "${widget.seance.prenomCoordonnes} ${widget.seance.nomCoordonnees}",
                            ),

                            const SizedBox(
                              height: 10.0,
                            ),

                            //////////////////////////////////////////
                            // Lieu ///////////////////////////////////
                            ModifChampDeSeance(
                              myLabel: "Adresse",
                              myData:
                                  "${widget.seance.nomAdresse} ${widget.seance.complementAdresse} ${widget.seance.nomVille} ${widget.seance.codePostal}",
                              is_editabled: is_Editing,
                            ),

                            const SizedBox(
                              height: 10.0,
                            ),

                            //////////////////////////////////////////
                            // Parcours //////////////////////////////
                            MonChampDeSeance(
                              myLabel: "Parcours",
                              myData: widget.seance.typeParcours ?? "",
                            ),

                            const SizedBox(
                              height: 15.0,
                            ),

                            //////////////////////////////////////////
                            // Type //////////////////////////
                            MonChampDeSeance(
                              myLabel: "Type",
                              myData: widget.seance.typeSeance ?? "",
                            ),

                            const SizedBox(
                              height: 15.0,
                            ),

                            // //////////////////////////////////////////
                            // // Date /////////////////////////////////
                            // ModifChampDeSeance(
                            //   myLabel: "Date",
                            //   myData: "${widget.seance.from}",
                            //   is_editabled: is_Editing,
                            // ),

                            // const SizedBox(
                            //   height: 15.0,
                            // ),

                            //////////////////////////////////////////
                            // Debut ///////////////////////////////
                            ModifChampDeSeance(
                              myLabel: "Début",
                              myData: "${widget.seance.from}",
                              is_editabled: is_Editing,
                            ),

                            const SizedBox(
                              height: 15.0,
                            ),

                            //////////////////////////////////////////
                            // Fin ////////////////////////////
                            ModifChampDeSeance(
                              myLabel: "Fin",
                              myData: "${widget.seance.to}",
                              is_editabled: is_Editing,
                            ),

                            const SizedBox(
                              height: 15.0,
                            ),

                            //////////////////////////////////////////
                            // Etat ///////////////////////////
                            MonChampDeSeance(
                              myLabel: "Séance émargée ?",
                              myData: "${widget.seance.validationSeance}",
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
                                    Radius.circular(100.0)),
                                color: Theme.of(context).primaryColorLight,
                              ),
                              width: 115.0,
                              height: 40.0,
                              child: ElevatedButton(
                                style: ButtonStyle(
                                  shape: MaterialStateProperty.all<
                                          RoundedRectangleBorder>(
                                      RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(10.0),
                                  )),
                                  backgroundColor:
                                      MaterialStateProperty.all<Color>(
                                          Theme.of(context).primaryColorLight),
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
                                        child: Icon(Icons.create_rounded,
                                            color:
                                                Theme.of(context).primaryColor),
                                      ),
                                      const SizedBox(
                                        width: 5.0,
                                      ),
                                      Text(
                                        'Modifier',
                                        style: TextStyle(
                                          color: Theme.of(context).primaryColor,
                                        ),
                                      ),
                                    ]),
                              )),
                          const SizedBox(
                            width: 25.0,
                          ),
                          Container(
                              decoration: BoxDecoration(
                                borderRadius: const BorderRadius.all(
                                    Radius.circular(100.0)),
                                color: Theme.of(context).primaryColorLight,
                              ),
                              width: 115.0,
                              height: 40.0,
                              child: ElevatedButton(
                                style: ButtonStyle(
                                  shape: MaterialStateProperty.all<
                                          RoundedRectangleBorder>(
                                      RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(10.0),
                                  )),
                                  backgroundColor:
                                      MaterialStateProperty.all<Color>(
                                          Theme.of(context).primaryColorLight),
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
                                        child: Icon(Icons.check,
                                            color:
                                                Theme.of(context).primaryColor),
                                      ),
                                      const SizedBox(
                                        width: 5.0,
                                      ),
                                      Text(
                                        'Valider',
                                        style: TextStyle(
                                          color: Theme.of(context).primaryColor,
                                        ),
                                      ),
                                    ]),
                              )),
                          const SizedBox(
                            width: 25.0,
                          ),
                          Container(
                              decoration: BoxDecoration(
                                borderRadius: const BorderRadius.all(
                                    Radius.circular(100.0)),
                                color: Theme.of(context).primaryColorLight,
                              ),
                              width: 115.0,
                              height: 40.0,
                              child: ElevatedButton(
                                style: ButtonStyle(
                                  shape: MaterialStateProperty.all<
                                          RoundedRectangleBorder>(
                                      RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(10.0),
                                  )),
                                  backgroundColor:
                                      MaterialStateProperty.all<Color>(
                                          Theme.of(context).primaryColorLight),
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
                                        child: Icon(Icons.close,
                                            color:
                                                Theme.of(context).primaryColor),
                                      ),
                                      const SizedBox(
                                        width: 5.0,
                                      ),
                                      Text(
                                        'Annuler',
                                        style: TextStyle(
                                          color: Theme.of(context).primaryColor,
                                        ),
                                      ),
                                    ]),
                              )),
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
                                    Radius.circular(100.0)),
                                color: Theme.of(context).primaryColorLight,
                              ),
                              width: 115.0,
                              height: 40.0,
                              child: ElevatedButton(
                                style: ButtonStyle(
                                  shape: MaterialStateProperty.all<
                                          RoundedRectangleBorder>(
                                      RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(10.0),
                                  )),
                                  backgroundColor:
                                      MaterialStateProperty.all<Color>(
                                          Theme.of(context).primaryColorLight),
                                ),
                                onPressed: () {
                                  setState(() {
                                    Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (context) =>
                                            Emargement(seance: widget.seance),
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
                                          color: Theme.of(context).primaryColor,
                                        ),
                                      ),
                                    ]),
                              )),

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
