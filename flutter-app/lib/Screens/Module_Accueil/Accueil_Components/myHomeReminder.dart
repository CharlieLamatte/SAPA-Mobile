import 'package:flutter/material.dart';

class MyHomeReminder extends StatefulWidget {
  const MyHomeReminder({
    Key? key
  }) : super(key: key);

  @override
  _MyHomeReminderState createState() => _MyHomeReminderState();
}

class _MyHomeReminderState extends State<MyHomeReminder> {
  String type = "Jujitsu Self Defense";
  String nom_lieu_pratique = "Buxerolles";
  String nom_jour = "Lundi";
  String heure = "10h30";

  @override
  Widget build(BuildContext context) {
    return
      Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            ElevatedButton(
              style: TextButton.styleFrom(
                foregroundColor: Theme.of(context).splashColor, padding: const EdgeInsets.symmetric(
                    vertical: 15.0,
                    horizontal: 15.0
                ),
                backgroundColor: Theme.of(context).primaryColor,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(20.0),
                ),
                textStyle: const TextStyle(
                  fontSize: 20,
                  // fontWeight: FontWeight.bold,
                ),
              ),
              child: Column(
                children: [
                  Text(
                    "Ma prochaine séance",
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 23,
                      fontWeight: FontWeight.bold,
                      color: Theme.of(context).splashColor,
                      shadows: const [
                        Shadow(
                          offset: Offset(0.0, 0.0),
                          blurRadius: 15.0,
                          color: Color.fromARGB(255, 50, 50, 50),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(
                    height: 5.0,
                  ),
                  Text(
                    "Activité : $type  \nDate : $nom_jour à $heure \nLieu : $nom_lieu_pratique ",
                    textAlign: TextAlign.justify,
                  ),
                ],
              ),
              onPressed: () => // TODO MyHomePage => rappel de la prochaine séance

              // START : pop-up de construction //
              showDialog(
                  context: context,
                  builder: (BuildContext _) {
                    return const AlertDialog(
                      title: Text('🔨 Information 🔨'),
                      content: Text(
                          'Faire un lien vers une page de détail sur l\'activité (à construire) qui sera dans le module Mon Suivi PEPS'
                      ),
                    );
                  }
              ),
              // END : pop-up de construction //
            ),
          ]
      );
  }
}
