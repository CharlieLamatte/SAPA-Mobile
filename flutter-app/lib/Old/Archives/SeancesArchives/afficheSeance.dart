import 'package:flutter/material.dart';

import 'package:flutter_application_3/old/Archives/SeancesArchives/AjoutExercice.dart';


void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  // This widget is the root of your application.
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Ma Seance',
      theme: ThemeData(
        primarySwatch: Colors.teal,
      ),
      home: const maSeance(title: 'Ma Seance'),
    );
  }
}
class maSeance extends StatefulWidget {
  const maSeance({Key? key, required this.title}) : super(key: key);

  final String title;

  @override
  State<maSeance> createState() => _maSeanceState();
}

class _maSeanceState extends State<maSeance> {

  @override
  Widget build(BuildContext context) {
    return SafeArea(
        child: Scaffold(
          // App bar ///////////////////////////////////////////////////////////
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
          body: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(15.0, 0.0, 15.0, 20.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [

                  // Titre ///////////////////////////////////////////
                  RichText(
                    text: TextSpan(
                      text: "ma séance".toUpperCase(),
                      style: const TextStyle(
                        color: Color(0xFF001e1b),
                        fontSize: 26.0,
                      ),
                    ),
                  ),
                  const Divider(
                    color: Colors.teal,
                    thickness: 1.5,
                    indent: 10,
                    endIndent: 40,
                  ),

                  // photo de séance /////////////////////////////////
                  Center(
                    child: Container(
                      width: 150.0,
                      height: 150.0,
                      alignment: Alignment.center,
                      margin: const EdgeInsets.fromLTRB(0.0, 20.0, 0.0, 0.0),
                      decoration: const BoxDecoration(
                        shape: BoxShape.circle,
                        image: DecorationImage(
                          fit: BoxFit.fill,
                          image: AssetImage("assets/musculation.jpg"),
                        ),
                      ),
                    ),
                  ),

                  const SizedBox(height: 20),

                  // Partie infos séance ///////////////////////

                  // Titre et crayon de modification ///////
                  Row(
                    children: [
                      Expanded(
                        flex: 4,
                        child: Container(
                          width: 250.0,
                          margin: const EdgeInsets.symmetric(
                            vertical: 20.0,
                          ),
                          padding: const EdgeInsets.symmetric(
                            horizontal : 20.0,
                            vertical: 2.0,
                          ),
                          alignment: Alignment.center,
                          decoration: BoxDecoration(
                            color: Theme.of(context).primaryColor,
                            border: Border.all(
                                color: Theme.of(context).primaryColor,
                                width: 2.0
                            ),
                            borderRadius: const BorderRadius.all(
                                Radius.circular(5.0)
                            ),
                          ),
                          child : RichText(
                            text: const TextSpan(
                              text: "Informations sur la séance",
                              style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                      Expanded(
                        flex: 1,
                        child: Align(
                          alignment: Alignment.centerRight,
                          // padding: EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 0.0),
                          child: IconButton(
                            icon: const Icon(Icons.create_rounded),
                            color: Colors.teal,
                            onPressed: () => {}, // TODO
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 15),


                  // Date ////////////////////////////////
                  Row(
                    children: [
                      Expanded(
                        flex: 1,
                        child: Container(
                          margin: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 10.0),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10.0,
                          ),
                          alignment: Alignment.centerLeft,
                          child : RichText(
                            text: const TextSpan(
                              text: "Date",
                              style: TextStyle(
                                color: Colors.black54,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                      Expanded(
                        flex: 2,
                        child: Container(
                          margin: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 10.0),
                          padding: const EdgeInsets.fromLTRB(0.0, 0.0, 15.0, 0.0),
                          alignment: Alignment.centerRight,
                          decoration: BoxDecoration(
                            color: const Color(0xFFD6D6D6),
                            border: Border.all(
                                color: const Color(0xFFD6D6D6),
                                width: 2.0
                            ),
                            borderRadius: const BorderRadius.all(
                                Radius.circular(5.0)
                            ),
                          ),
                          child : RichText(
                            text: const TextSpan(
                              text: "19/01/2022",
                              style: TextStyle(
                                color: Colors.black54,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 15),

                  // Durée ///////////////////////////////////
                  Row(
                    children: [
                      Expanded(
                        flex: 1,
                        child: Container(
                          margin: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 10.0),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10.0,
                          ),
                          alignment: Alignment.centerLeft,
                          child : RichText(
                            text: const TextSpan(
                              text: "Durée",
                              style: TextStyle(
                                color: Colors.black54,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                      Expanded(
                        flex: 2,
                        child: Container(
                          margin: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 10.0),
                          padding: const EdgeInsets.fromLTRB(0.0, 0.0, 15.0, 0.0),
                          alignment: Alignment.centerRight,
                          decoration: BoxDecoration(
                            color: const Color(0xFFD6D6D6),
                            border: Border.all(
                                color: const Color(0xFFD6D6D6),
                                width: 2.0
                            ),
                            borderRadius: const BorderRadius.all(
                                Radius.circular(5.0)
                            ),
                          ),
                          child : RichText(
                            text: const TextSpan(
                              text: "40 min 20 s",
                              style: TextStyle(
                                color: Colors.black54,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 15),

                  // Commentaire ///////////////////////////////////
                  Row(
                    children: [
                      Expanded(
                        flex: 1,
                        child: Container(
                          margin: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 10.0),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10.0,
                          ),
                          alignment: Alignment.centerLeft,
                          child : RichText(
                            text: const TextSpan(
                              text: "Commentaire",
                              style: TextStyle(
                                color: Colors.black54,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                      Expanded(
                        flex: 2,
                        child: Container(
                          margin: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 10.0),
                          padding: const EdgeInsets.fromLTRB(0.0, 0.0, 15.0, 0.0),
                          alignment: Alignment.centerRight,
                          decoration: BoxDecoration(
                            color: const Color(0xFFD6D6D6),
                            border: Border.all(
                                color: const Color(0xFFD6D6D6),
                                width: 2.0
                            ),
                            borderRadius: const BorderRadius.all(
                                Radius.circular(5.0)
                            ),
                          ),
                          child : RichText(
                            text: const TextSpan(
                              text: "J'ai réussi à aller au bout de la séance sans trop de difficultés",
                              style: TextStyle(
                                color: Colors.black54,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 20),

                  // Partie Exercice ////////////////////////////////

                  // Titre et crayon de modification ///////
                  Row(
                    children: [
                      Expanded(
                        flex: 4,
                        child: Container(
                          width: 250.0,
                          margin: const EdgeInsets.symmetric(
                            vertical: 20.0,
                          ),
                          padding: const EdgeInsets.symmetric(
                            horizontal : 20.0,
                            vertical: 2.0,
                          ),
                          alignment: Alignment.center,
                          decoration: BoxDecoration(
                            color: Theme.of(context).primaryColor,
                            border: Border.all(
                                color: Theme.of(context).primaryColor,
                                width: 2.0
                            ),
                            borderRadius: const BorderRadius.all(
                                Radius.circular(5.0)
                            ),
                          ),
                          child : RichText(
                            text: const TextSpan(
                              text: "Exercice n°X",
                              style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                      Expanded(
                        flex: 1,
                        child: Align(
                          alignment: Alignment.centerRight,
                          // padding: EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 0.0),
                          child: IconButton(
                            icon: const Icon(Icons.create_rounded),
                            color: Colors.teal,
                            onPressed: () => {}, // TODO
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 15),


                  // Temps ////////////////////////////////
                  Row(
                    children: [
                      Expanded(
                        flex: 1,
                        child: Container(
                          margin: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 10.0),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10.0,
                          ),
                          alignment: Alignment.centerLeft,
                          child : RichText(
                            text: const TextSpan(
                              text: "Temps",
                              style: TextStyle(
                                color: Colors.black54,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                      Expanded(
                        flex: 2,
                        child: Container(
                          margin: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 10.0),
                          padding: const EdgeInsets.fromLTRB(0.0, 0.0, 15.0, 0.0),
                          alignment: Alignment.centerRight,
                          decoration: BoxDecoration(
                            color: const Color(0xFFD6D6D6),
                            border: Border.all(
                                color: const Color(0xFFD6D6D6),
                                width: 2.0
                            ),
                            borderRadius: const BorderRadius.all(
                                Radius.circular(5.0)
                            ),
                          ),
                          child : RichText(
                            text: const TextSpan(
                              text: "1 min 45",
                              style: TextStyle(
                                color: Colors.black54,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 15),

                  // Distance /////////////////////////////////
                  Row(
                    children: [
                      Expanded(
                        flex: 1,
                        child: Container(
                          margin: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 10.0),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10.0,
                          ),
                          alignment: Alignment.centerLeft,
                          child : RichText(
                            text: const TextSpan(
                              text: "Distance",
                              style: TextStyle(
                                color: Colors.black54,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                      Expanded(
                        flex: 2,
                        child: Container(
                          margin: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 10.0),
                          padding: const EdgeInsets.fromLTRB(0.0, 0.0, 15.0, 0.0),
                          alignment: Alignment.centerRight,
                          decoration: BoxDecoration(
                            color: const Color(0xFFD6D6D6),
                            border: Border.all(
                                color: const Color(0xFFD6D6D6),
                                width: 2.0
                            ),
                            borderRadius: const BorderRadius.all(
                                Radius.circular(5.0)
                            ),
                          ),
                          child : RichText(
                            text: const TextSpan(
                              text: "200-250 m",
                              style: TextStyle(
                                color: Colors.black54,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 15),

                  // Charge ///////////////////////////////////
                  Row(
                    children: [
                      Expanded(
                        flex: 1,
                        child: Container(
                          margin: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 10.0),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10.0,
                          ),
                          alignment: Alignment.centerLeft,
                          child : RichText(
                            text: const TextSpan(
                              text: "Charge",
                              style: TextStyle(
                                color: Colors.black54,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                      Expanded(
                        flex: 2,
                        child: Container(
                          margin: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 10.0),
                          padding: const EdgeInsets.fromLTRB(0.0, 0.0, 15.0, 0.0),
                          alignment: Alignment.centerRight,
                          decoration: BoxDecoration(
                            color: const Color(0xFFD6D6D6),
                            border: Border.all(
                                color: const Color(0xFFD6D6D6),
                                width: 2.0
                            ),
                            borderRadius: const BorderRadius.all(
                                Radius.circular(5.0)
                            ),
                          ),
                          child : RichText(
                            text: const TextSpan(
                              text: "2 kg",
                              style: TextStyle(
                                color: Colors.black54,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 15),

                  // Ressenti ///////////////////////////////////
                  Row(
                    children: [
                      Expanded(
                        flex: 1,
                        child: Container(
                          margin: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 10.0),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10.0,
                          ),
                          alignment: Alignment.centerLeft,
                          child : RichText(
                            text: const TextSpan(
                              text: "Ressenti",
                              style: TextStyle(
                                color: Colors.black54,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ),
                      ),
                      Expanded(
                        flex: 2,
                        child: Container(
                          margin: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 10.0),
                          padding: const EdgeInsets.fromLTRB(0.0, 0.0, 15.0, 0.0),
                          alignment: Alignment.centerRight,
                          decoration: BoxDecoration(
                            color: const Color(0xFFD6D6D6),
                            border: Border.all(
                                color: const Color(0xFFD6D6D6),
                                width: 2.0
                            ),
                            borderRadius: const BorderRadius.all(
                                Radius.circular(5.0)
                            ),
                          ),
                          child : const Icon(
                            Icons.mood,
                            color: Colors.green,
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 15),

                  // Bouton ajouter exercice ///////////////////////////////////////////////////////

                  Row(
                    children: [
                      Expanded(
                        flex: 1,
                        child: Align(
                          alignment: Alignment.bottomRight,
                          // padding: EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 0.0),
                        child: TextButton(
                          style: ButtonStyle(
                            foregroundColor:
                                MaterialStateProperty.all<Color>(Colors.teal),
                          ),
                          onPressed: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) => const ajoutExercice(
                                  title: 'Ajouter Exercice',
                                ),
                              ),
                            );
                          },
                          child: const Text('Ajouter un exercice'),
                        ),
                        ),
                      ),
                    ],
                  ),

                ],
              ),
            ),
          ),
        ),
    );


  }
}
