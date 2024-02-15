import 'dart:math';

import 'package:expandable/expandable.dart';
import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Components/maNavDrawer.dart';
import 'package:flutter_application_3/Screens/Components/monAppBar.dart';
import 'package:flutter_application_3/Old/Module_Profil/profil/screens/pageProfil/monTitreDeCategorie.dart';
import 'package:flutter_application_3/Screens/Module_Profil/Profil_Components/monChampDuProfil.dart';
import 'package:image_picker/image_picker.dart';

class MonProfile extends StatefulWidget {

  const MonProfile({Key? key}) : super(key: key);

  @override
  State<MonProfile> createState() => _MonProfileState();
}

class _MonProfileState extends State<MonProfile> {

  final bool _photo = true;

  final String _pdp = "assets/kirua.jpg";
  TextEditingController dateinput = TextEditingController();

  /*List infoPersoList = [
    {"label":"prenom",               "data":"Kirua"},
    {"label":"nom",                  "data":"Zoldick"},
    {"label":"Sexe",                 "data":"Homme"},
    {"label":"Date de naissance",    "data":"07/12/2009"},
    {"label":"Téléphone Fixe",       "data":"0201010101"},
    {"label":"Téléphone Portable",   "data":"0601010101"},
    {"label":"Email",                "data":"kz@gmail.com"},
    {"label":"Adresse",              "data":"1 rue des petits bonhommes"},
    {"label":"Complément d'adresse", "data":""},
    {"label":"Code Postal",          "data":"00000"},
    {"label":"Ville",                "data":"Hunter City"}
  ];
  var infoPersoLabelList = [
    "Prénom",
    "Nom",
    "Sexe",
    "Date de naissance",
    "Téléphone fixe",
    "Téléphone portable",
    "Email",
    "Adresse",
    "Complément d'adresse",
    "Code Postal",
    "Ville"
  ];
  var infoPersoDataList = [
    "Kirua",
    "Zoldick",
    "Homme",
    "07/12/2009",
    "0201010101",
    "0601010101",
    "kz@gmail.com",
    "1 rue des petits bonhommes",
    "",
    "00000",
    "Hunter City",
  ];*/

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

  final double _taille = 1.58;
  final double _poids = 49.0;

  // calcul IMC
  calculIMC(double taille, double poids) {
    return (poids/pow(taille, 2)).toStringAsFixed(1);
  }

  @override
  Widget build(BuildContext context) {

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

    List<List<String>> contact = [
      cuPrenom,
      cuNom,
      cuTelFixe,
      cuTelPort,
    ];

    var IMC = calculIMC(_taille, _poids);
    mcImc.add(IMC);

    List<List<String>> monCorps = [
      mcTaille,
      mcPoids,
      mcImc,
    ];

    // Fetch content from the json file
    // Future<void> readJson() async {
    //   final String response = await rootBundle.loadString('assets/json/monBeneficiaireKirua.json');
    //   final data = await json.decode(response);
    //   setState(() {
    //     infoPersoList = data["beneficiaire.infoPerso"];
    //   });
    // }

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
                // Titre /////////////////////////////////////////////////////////
                //   MonTitreDePage(myTitle: "mon profil"),

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
                                image:
                                _photo
                                    ? AssetImage(_pdp)
                                    : const AssetImage("assets/avatar.png"),
                              ),
                            ),
                            child: Align(
                              alignment: Alignment.bottomRight,
                              child: Container(
                                width: 40.0,
                                height: 40.0,
                                padding: const EdgeInsets.all(0.0),
                                decoration:
                                _photo
                                    ? BoxDecoration(
                                  color: Theme.of(context).primaryColor,
                                  shape: BoxShape.circle,
                                )
                                    : BoxDecoration(
                                  color: Theme.of(context).primaryColorLight,
                                  shape: BoxShape.circle,
                                ),
                                child: IconButton(
                                  icon:
                                  _photo
                                      ? const Icon(Icons.create_rounded)
                                      : const Icon(Icons.add_a_photo_rounded),
                                  color:
                                  _photo
                                      ? Colors.white // TODO couleur du thème
                                      : Colors.black54, // TODO couleur du thème
                                  onPressed: () => // TODO MonProfil => changer pdp
                                  // START : pop-up de construction //
                                  showDialog(
                                      context: context,
                                      builder: (BuildContext _) {
                                        return const AlertDialog(
                                          title: Text('🔨 Information'),
                                          content: Text(
                                            'Cette page est en cours de construction !',
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
                                  child :
                                  MonTitreDeCategorie(
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
                              /*for (var value in infoPersoList)
                                Column(
                                  children:[
                                    const SizedBox(
                                      height: 15.0,
                                    ),
                                    MonChampDuProfil( // TODO ne fonctionne pas !!!!!!!!!!!!!!!!!
                                      myLabel: infoPersoLabelList[value]["label"],
                                      myData: infoPersoDataList[value]["data"],
                                      space: 15.0,
                                    ),
                                  ]
                                ),*/

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // prenom ///////////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[0][0],
                                myData: infoPerso[0][1],
                                space: 15.0,
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // nom ///////////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[1][0],
                                myData: infoPerso[1][1],
                                space: 15.0,
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // sexe //////////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[2][0],
                                myData: infoPerso[2][1],
                                space: 15.0,
                              ),

                              const SizedBox(
                                height: 10.0,
                              ),

                              //////////////////////////////////////////
                              // DDN ///////////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[3][0],
                                myData: infoPerso[3][1],
                                space: 15.0,
                              ),

                              const SizedBox(
                                height: 10.0,
                              ),

                              //////////////////////////////////////////
                              // tel fixe //////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[4][0],
                                myData: infoPerso[4][1],
                                space: 15.0,
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // tel portable //////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[5][0],
                                myData: infoPerso[5][1],
                                space: 15.0,
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // email /////////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[6][0],
                                myData: infoPerso[6][1],
                                space: 15.0,
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // adresse ///////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[7][0],
                                myData: infoPerso[7][1],
                                space: 15.0,
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // complement ////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[8][0],
                                myData: infoPerso[8][1],
                                space: 15.0,
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // code postal ///////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[9][0],
                                myData: infoPerso[9][1],
                                space: 15.0,
                              ),

                              const SizedBox(
                                height: 15.0,
                              ),

                              //////////////////////////////////////////
                              // ville /////////////////////////////////
                              MonChampDuProfil(
                                myLabel: infoPerso[10][0],
                                myData: infoPerso[10][1],
                                space: 15.0,
                              ),
                            ],
                          ),
                        ),

                        /*if (_infoP) ... [*/
                        // expanded si il y a aucune info mais bon...
                        /*]
                      else ... [
                        /////////////////////////////////////////////
                        // Bouton créer /////////////////////////////
                        Center(
                          child: Container(
                            width: 40.0,
                            height: 40.0,
                            padding: EdgeInsets.all(0.0),
                            decoration: BoxDecoration(
                              color: Theme.of(context).primaryColorLight,
                              shape: BoxShape.circle,
                            ),
                            child: IconButton(
                              icon: Icon(Icons.add),
                              color: Colors.black54,
                              onPressed: () => widget.onChangedStep(2),
                            ),
                          ),
                        ),
                      ],*/

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

                                //////////////////////////////////////////
                                // prenom ///////////////////////////////////
                                MonChampDuProfil(
                                  myLabel: contact[0][0],
                                  myData: contact[0][1],
                                  space: 15.0,
                                ),

                                const SizedBox(
                                  height: 15.0,
                                ),

                                //////////////////////////////////////////
                                // nom ///////////////////////////////////
                                MonChampDuProfil(
                                  myLabel: contact[1][0],
                                  myData: contact[1][1],
                                  space: 15.0,
                                ),

                                const SizedBox(
                                  height: 15.0,
                                ),

                                //////////////////////////////////////////
                                // tel fixe //////////////////////////////
                                MonChampDuProfil(
                                  myLabel: contact[2][0],
                                  myData: contact[2][1],
                                  space: 15.0,
                                ),

                                const SizedBox(
                                  height: 10.0,
                                ),

                                //////////////////////////////////////////
                                // tel portable //////////////////////////
                                MonChampDuProfil(
                                  myLabel: contact[3][0],
                                  myData: contact[3][1],
                                  space: 15.0,
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

                                ////////////////////////////
                                // Taille //////////////////
                                MonChampDuProfil(
                                  myLabel: monCorps[0][0],
                                  myData: monCorps[0][1],
                                  space: 15.0,
                                ),

                                const SizedBox(
                                  height: 15.0,
                                ),

                                /////////////////////////////
                                // Poids ////////////////////
                                MonChampDuProfil(
                                  myLabel: monCorps[1][0],
                                  myData: monCorps[1][1],
                                  space: 15.0,
                                ),

                                const SizedBox(
                                  height: 15.0,
                                ),

                                /////////////////////////////
                                // IMC //////////////////////
                                MonChampDuProfil(
                                  myLabel: monCorps[2][0],
                                  myData: monCorps[2][1],
                                  space: 15.0,
                                ),

                                const SizedBox(
                                  height: 15.0,
                                ),

                                Container(
                                  decoration: BoxDecoration(
                                    borderRadius: const BorderRadius.all(
                                        Radius.circular(100.0)
                                    ),
                                    color: Theme.of(context).primaryColorLight,
                                  ),
                                  width: 115.0,
                                  height: 40.0,
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
                                        )
                                    ),
                                    child: Row(
                                        children: [
                                          InkWell(
                                            child: Icon(
                                                Icons.create_rounded,
                                                color: Theme.of(context).primaryColor),
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

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class OpenImagePicker {
  final ImageSource imageSource;

  OpenImagePicker({required this.imageSource});

}
