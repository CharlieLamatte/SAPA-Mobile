import 'dart:math';

import 'package:expandable/expandable.dart';
import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Components/maNavDrawer.dart';
import 'package:flutter_application_3/Screens/Components/monAppBar.dart';
import 'package:flutter_application_3/Screens/Components/monTitreDeCategorie.dart';

import 'Profil_Components/monChampDuProfil.dart';
import 'monCorpsForm.dart';

class MonProfile extends StatefulWidget {

  const MonProfile({Key? key, }) : super(key: key);

  @override
  State<MonProfile> createState() => _MonProfileState();
}

class _MonProfileState extends State<MonProfile> {

  final String _pdp = "assets/kirua.jpg";

  // listes "Informations personnelles"
  List<String> ipPrenom     = ["Prénom", "Kirua"];
  List<String> ipNom        = ["Nom", "Zoldick"];
  List<String> ipSexe       = ["Sexe", "Homme"];
  List<String> ipDdn        = ["Date de naissance", "07/12/2009"];
  List<String> ipTelFixe    = ["Téléphone fixe", "0201010101"];
  List<String> ipTelPort    = ["Téléphone portable", "0601010101"];
  List<String> ipEmail      = ["Email", "kz@gmail.com"];
  List<String> ipAdresse    = ["Adresse", "1 rue des petits bonhommes"];
  List<String> ipComplement = ["Complément d'adresse", ""];
  List<String> ipCp         = ["Code postal", "00000"];
  List<String> ipVille      = ["Ville", "Hunter City"];

  // listes "Contact en cas d'urgence"
  List<String> cuPrenom  = ["Prénom", "Gon"];
  List<String> cuNom     = ["Nom", "Freecss"];
  List<String> cuTelFixe = ["Téléphone fixe", ""];
  List<String> cuTelPort = ["Téléphone portable", "0602020202"];

  // listes "Mon Corps"
  List<String> mcTaille = ["Taille", "1.58 m"];
  List<String> mcPoids  = ["Poids", "49.0 kg"];
  List<String> mcImc    = ["IMC"];
  // pour le calcul de l'IMC
  final double _taille = 1.58;
  final double _poids = 49.0;

  // calcul IMC
  calculIMC(double taille, double poids) {
    return (poids/pow(taille, 2)).toStringAsFixed(1);
  }

  @override
  Widget build(BuildContext context) {

    // Liste "Informations personnelles"
    List<List<String>> infoPerso = [
      ipPrenom,
      ipNom,
      ipSexe,
      ipDdn,
      ipTelFixe,
      ipTelPort,
      ipEmail,
      ipAdresse,
      ipComplement,
      ipCp,
      ipVille,
    ];

    // Liste "Contact en cas d'urgence"
    List<List<String>> contact = [
      cuPrenom,
      cuNom,
      cuTelFixe,
      cuTelPort,
    ];

    var imc = calculIMC(_taille, _poids);
    mcImc.add(imc);

    List<List<String>> monCorps = [
      mcTaille,
      mcPoids,
      mcImc,
    ];

    return SafeArea(
      child: Scaffold(

        //////////////////////////////////////////////////////////////////////
        // App bar ///////////////////////////////////////////////////////////
        appBar: MonAppBar(myTitle: 'Mon Profil'),
        drawer: const MyNavDrawer(),

        body: Padding(
          padding: const EdgeInsets.only(
              left: 25.0,
              top: 15.0,
              right: 25.0,
              bottom: 15.0
          ),
          child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch, // étire sur tt l'écran
              children: [

                //////////////////////////////////////////////////////////////////
                // Scroll view ///////////////////////////////////////////////////
                Expanded(
                  child: SingleChildScrollView(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [

                        ////////////////////////////////////////////////////
                        // Photo de profil /////////////////////////////////
                        Center(
                          child: Container(
                            width: 150.0,
                            height: 150.0,
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              image: DecorationImage(
                                  fit: BoxFit.fill,
                                  image: AssetImage(_pdp)
                              ),
                            ),
                            child: Align(
                              alignment: Alignment.bottomRight,
                              child: Container(
                                width: 40.0,
                                height: 40.0,
                                padding: const EdgeInsets.all(0.0),
                                decoration: BoxDecoration(
                                  color: Theme.of(context).primaryColor,
                                  shape: BoxShape.circle,
                                ),
                                child: IconButton(
                                  icon:  const Icon(Icons.create_rounded),
                                  color:  Theme.of(context).splashColor,
                                  onPressed: () => // TODO MonProfil => changer pdp
                                  // START : pop-up de construction //
                                  showDialog(
                                      context: context,
                                      builder: (BuildContext _) {
                                        return const AlertDialog(
                                          title: Text('🔨 Information'),
                                          content: Text(
                                            'Faire le changement de la pdp si possible (à voir avec la BDD pck il n\'y a pas de champ prévu)',
                                          ),
                                        );
                                      }
                                  ),
                                  // END : pop-up de construction //
                                ),
                              ),
                            ),
                          ),
                        ),

                        const SizedBox(
                          height: 20.0,
                        ),

                        ////////////////////////////////////////////////////
                        // Partie infos personnelles ///////////////////////
                        ExpandablePanel(
                          // Titre de la partie //////////////////////
                          header:
                          const Row(
                              children: [
                                SizedBox(
                                  height: 50.0,
                                ),
                                Expanded(
                                  child : MonTitreDeCategorie(
                                    myLabelTitle: "Informations personnelles",
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

                              // TODO : automatiser (avec une boucle for ou foreach ?) l'affichage des infos

                              // prenom ///////////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[0][0],
                                myData: infoPerso[0][1],
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // nom ///////////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[1][0],
                                myData: infoPerso[1][1],
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // sexe //////////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[2][0],
                                myData: infoPerso[2][1],
                              ),

                              const SizedBox(
                                height: 10.0,
                              ),

                              //////////////////////////////////////////
                              // DDN ///////////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[3][0],
                                myData: infoPerso[3][1],
                              ),

                              const SizedBox(
                                height: 10.0,
                              ),

                              //////////////////////////////////////////
                              // tel fixe //////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[4][0],
                                myData: infoPerso[4][1],
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // tel portable //////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[5][0],
                                myData: infoPerso[5][1],
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // email /////////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[6][0],
                                myData: infoPerso[6][1],
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // adresse ///////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[7][0],
                                myData: infoPerso[7][1],
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // complement ////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[8][0],
                                myData: infoPerso[8][1],
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // code postal ///////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[9][0],
                                myData: infoPerso[9][1],
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // ville /////////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[10][0],
                                myData: infoPerso[10][1],
                              ),
                            ],
                          ),
                        ),

                        const SizedBox(
                          height: 20.0,
                        ),

                        ////////////////////////////////////////////////////
                        // Partie Contact //////////////////////////////////
                        ExpandablePanel(
                          // Titre de la partie //////////////////////
                            header:
                            const Row(
                                children: [
                                  SizedBox(
                                    height: 50.0,
                                  ),

                                  Expanded(
                                    child : MonTitreDeCategorie(
                                      myLabelTitle: "Contact en cas d'urgence",
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

                                // TODO : automatiser (avec une boucle for ou foreach ?) l'affichage des infos

                                //////////////////////////////////////////
                                // prenom ////////////////////////////////
                                MonChampDuProfil(
                                  myLabel: contact[0][0],
                                  myData: contact[0][1],
                                ),

                                const SizedBox(
                                  height: 15.0,
                                ),

                                //////////////////////////////////////////
                                // nom ///////////////////////////////////
                                MonChampDuProfil(
                                  myLabel: contact[1][0],
                                  myData: contact[1][1],
                                ),

                                const SizedBox(
                                  height: 15.0,
                                ),

                                //////////////////////////////////////////
                                // tel fixe //////////////////////////////
                                MonChampDuProfil(
                                  myLabel: contact[2][0],
                                  myData: contact[2][1],
                                ),

                                const SizedBox(
                                  height: 10.0,
                                ),

                                //////////////////////////////////////////
                                // tel portable //////////////////////////
                                MonChampDuProfil(
                                  myLabel: contact[3][0],
                                  myData: contact[3][1],
                                ),
                              ],
                            )
                        ),

                        const SizedBox(
                          height: 20.0,
                        ),

                        ////////////////////////////////////////////////////
                        // Partie Mon corps ////////////////////////////////
                        ExpandablePanel(
                          // Titre de la partie //////////////////////
                            header:
                            const Row(
                                children: [
                                  SizedBox(
                                    height: 50.0,
                                  ),
                                  Expanded(
                                    child : MonTitreDeCategorie(
                                      myLabelTitle: "Mon Corps",
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
                                  height: 10.0,
                                ),

                                // TODO : automatiser (avec une boucle for ou foreach ?) l'affichage des infos

                                ////////////////////////////
                                // Taille //////////////////
                                MonChampDuProfil(
                                  myLabel: monCorps[0][0],
                                  myData: monCorps[0][1],
                                ),

                                const SizedBox(
                                  height: 15.0,
                                ),

                                /////////////////////////////
                                // Poids ////////////////////
                                MonChampDuProfil(
                                  myLabel: monCorps[1][0],
                                  myData: monCorps[1][1],
                                ),

                                const SizedBox(
                                  height: 15.0,
                                ),

                                /////////////////////////////
                                // IMC //////////////////////
                                MonChampDuProfil(
                                  myLabel: monCorps[2][0],
                                  myData: monCorps[2][1],
                                ),

                                const SizedBox(
                                  height: 15.0,
                                ),

                                // Bouton de modification des infos sur "Mon Corps"
                                Container(
                                  decoration: BoxDecoration(
                                    borderRadius: const BorderRadius.all(
                                        Radius.circular(100.0)
                                    ),
                                    color: Theme.of(context).primaryColorLight,
                                  ),
                                  width: 115.0,
                                  height: 40.0,
                                  child:
                                    ElevatedButton(
                                      style: ButtonStyle(
                                        shape: MaterialStateProperty.all<RoundedRectangleBorder>(
                                            RoundedRectangleBorder(
                                                borderRadius: BorderRadius.circular(10.0)
                                            )
                                        ),
                                        backgroundColor: MaterialStateProperty.all<Color>(
                                            Theme.of(context).primaryColorLight
                                        ),
                                      ),

                                      onPressed: () => Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (context) => MonCorpsForm(
                                            taille: _taille,
                                            poids: _poids,
                                          ),
                                        ),
                                      ),

                                      child: Row(
                                          children: [
                                            InkWell(
                                              child: Icon(
                                                  Icons.create_rounded,
                                                  color: Theme.of(context).primaryColor
                                              ),
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
                                          ]
                                      ),
                                    )

/*
                                  child: RaisedButton(
                                    shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(10.0)
                                    ),
                                    textColor: Theme.of(context).primaryColor,
                                    color: Theme.of(context).primaryColorLight,
                                    elevation: 0,
                                    onPressed: () => Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (context) => MonCorpsForm(
                                          taille: _taille,
                                          poids: _poids,
                                        ),
                                      ),
                                    ),
                                    child: Row(
                                        children: [
                                          InkWell(
                                            child: Icon(
                                                Icons.create_rounded,
                                                color: Theme.of(context).primaryColor
                                            ),
                                          ),
                                          const SizedBox(
                                            width: 5.0,
                                          ),
                                          const Text('Modifier'),
                                        ]
                                    ),
                                  ),
*/
                                ),

                                const SizedBox(
                                  height: 15.0,
                                ),
                              ],
                            )
                        ),
                      ],
                    ),
                  ),
                ),
              ]
          ),
        ),
      ),
    );
  }
}
