import 'package:flutter/material.dart';
import 'package:flutter_application_3/old/views/pages/module_MaProgression/progression.dart';

// class HomePage extends StatefulWidget {
//   final Function() updateDarkThemeState;

//   HomePage(this.updateDarkThemeState);

//   @override
//   State<StatefulWidget> createState() => Reminder(updateDarkThemeState);
// }

class Reminder extends StatelessWidget {
  final Function() updateDarkThemeState;

  String type = "Jujitsu Self Defense";
  // ignore: non_constant_identifier_names
  String nom_lieu_pratique = "Buxerolles";
  // ignore: non_constant_identifier_names
  String nom_jour = "Lundi";
  //final heure = DateFormat("12:30");
  String heure = "10:30";
  Reminder(this.updateDarkThemeState, {Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 10, 20, 10),
      child: ElevatedButton(
        style: TextButton.styleFrom(
            foregroundColor: Colors.white, padding: const EdgeInsets.all(20.0),
            backgroundColor: const Color(0xFF077276),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20.0),
            ),
            textStyle: const TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
            )),
        child: Text(
          "Activité : $type  \nDate : $nom_jour à $heure \nLieu : $nom_lieu_pratique ",
          textAlign: TextAlign.justify,
        ),
        onPressed: () => Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => ProgressPage(updateDarkThemeState),
          ),
        ),
      ),
    );
  }
}
