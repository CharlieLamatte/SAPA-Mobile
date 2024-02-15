import 'package:datetime_picker_formfield/datetime_picker_formfield.dart';
import 'package:flutter/material.dart';
import 'package:flutter_application_3/old/delayed_animation.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

import 'afficheSeance.dart';

///////////////////////////////////////////////// Couleur du thème
const d_green = Colors.teal;

///////////////////////////////////////////////// Main
void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  // This widget is the root of your application.
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      /////////////////////////////////////////////////// gestion des langues sur le DatePicker
      localizationsDelegates: const [GlobalMaterialLocalizations.delegate],
      supportedLocales: const [Locale('en'), Locale('fr')],
      debugShowCheckedModeBanner: false,
      title: 'Ajouter une Séance',
      theme: ThemeData(
        primarySwatch: Colors.teal,
      ),
      home: const ajoutSeance(title: 'Ajouter une Séance'),
    );
  }
}

class ajoutSeance extends StatefulWidget {
  const ajoutSeance({Key? key, required this.title}) : super(key: key);

  final String title;

  @override
  State<ajoutSeance> createState() => _ajoutSeanceState();
}

class _ajoutSeanceState extends State<ajoutSeance> {
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
          ]),

      ///////////////////////////////////////////////// ScrollView
      body: SingleChildScrollView(
        child: Column(
          children: [
            Container(
              margin: const EdgeInsets.symmetric(
                vertical: 40,
                horizontal: 30,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ///////////////////////////////////////////////// Titre
                  DelayedAnimation(
                    //delay: 1500,
                    delay: 0,
                    child: Text(
                      "Nouvelle Séance".toUpperCase(),
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
                    endIndent: 50,
                  ),

                  const SizedBox(height: 22),

                  ///////////////////////////////////////////////// Description
                  DelayedAnimation(
                    //delay: 2500,
                    delay: 0,
                    child: Text(
                      "Veuillez renseigner les données concernant votre séance.",
                      style: GoogleFonts.poppins(
                        color: Colors.grey[600],
                        fontSize: 16,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 35),

            ///////////////////////////////////////////////// Form
            const SeanceForm(),

            const SizedBox(height: 125),

            ///////////////////////////////////////////////// Bouton Ajouter
            DelayedAnimation(
              //delay: 5500,
              delay: 0,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  shape: const StadiumBorder(), backgroundColor: d_green,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 125,
                    vertical: 13,
                  ),
                ),
                child: Text(
                  'AJOUTER',
                  style: GoogleFonts.poppins(
                    color: Colors.white,
                    fontSize: 15,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const maSeance(
                        title: 'Ma Seance',
                      ),
                    ),
                  );
                },
              ),
            ),
            const SizedBox(height: 90),
            Align(
              alignment: Alignment.centerRight,
              child: Padding(
                padding: const EdgeInsets.only(right: 35),
                child: TextButton(
                  onPressed: () {
                    Navigator.pop(context);
                  },

                  ///////////////////////////////////////////////// Annuler
                  child: DelayedAnimation(
                    //delay: 6500,
                    delay: 0,
                    child: Text(
                      "ANNULER",
                      style: GoogleFonts.poppins(
                        color: Colors.black,
                        fontSize: 18,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    ));
  }
}

///////////////////////////////////////////////// Mise en place du formulaire
class SeanceForm extends StatefulWidget {
  const SeanceForm({Key? key}) : super(key: key);

  @override
  _SeanceFormState createState() => _SeanceFormState();
}

class _SeanceFormState extends State<SeanceForm> {
  TextEditingController dateinput = TextEditingController();
  TextEditingController timeinput = TextEditingController();
  TextEditingController textinput = TextEditingController();
  final timeFormat = DateFormat("H:mm");
  Duration initialtimer = const Duration();

  var items = ['Marche', 'Course', 'Natation', 'Musculation', 'vélo'];


  @override
  void initState() {
    dateinput.text = ""; //set the initial value of text field
    timeinput.text = ""; //set the initial value of text field
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(
        horizontal: 30,
      ),
      child: Column(
        children: [
          ///////////////////////////////////////////////// Type de séance
          DelayedAnimation(
              //delay: 3500,
              delay: 0,
              child: TextField(
                controller: textinput,
                decoration: InputDecoration(
                  icon: const Icon(Icons.sports_handball),
                  labelText: 'Type',
                  labelStyle: TextStyle(
                    color: Colors.grey[400],
                  ),
                  suffixIcon: PopupMenuButton<String>(
                    icon: const Icon(Icons.arrow_drop_down),
                    onSelected: (String value) {
                      textinput.text = value;
                    },
                    itemBuilder: (BuildContext context) {
                      return items.map<PopupMenuItem<String>>((String value) {
                        return PopupMenuItem(
                            value: value,
                            child: Text(value));
                      }).toList();
                    },
                  ),
                ),
                readOnly: true,
              )
            ),
            
          const SizedBox(height: 30),

          ///////////////////////////////////////////////// Champ date
          DelayedAnimation(
            //delay: 3500,
            delay: 0,
            child: TextField(
              controller: dateinput,
              decoration: InputDecoration(
                icon: const Icon(Icons.calendar_today),
                labelText: 'Date',
                labelStyle: TextStyle(
                  color: Colors.grey[400],
                ),
              ),
              readOnly:
                  true, //set it true, so that user will not able to edit text
              onTap: () async {
                DateTime? pickedDate = await showDatePicker(
                    context: context,
                    locale: const Locale("fr", "FR"),
                    initialDate: DateTime.now(),
                    firstDate: DateTime(
                        2000), //DateTime.now() - not to allow to choose before today.
                    lastDate: DateTime(2101));
                if (pickedDate != null) {
                  print(
                      pickedDate); //pickedDate output format => 2021-03-10 00:00:00.000
                  String formattedDate =
                      DateFormat('dd/MM/yyyy').format(pickedDate);
                  print(
                      formattedDate); //formatted date output using intl package =>  2021-03-16
                  //you can implement different kind of Date Format here according to your requirement

                  setState(() {
                    dateinput.text =
                        formattedDate; //set output date to TextField value.
                  });
                } else {
                  print("Date non sélectionnée");
                }
              },
            ),
          ),

          const SizedBox(height: 30),

          ///////////////////////////////////////////////// Champ durée
          DelayedAnimation(
              //delay: 4500,
              delay: 0,
              child: DateTimeField(
                decoration: InputDecoration(
                  icon: const Icon(Icons.timer), //icon of text field
                  labelText: 'Durée',
                  labelStyle: TextStyle(
                    color: Colors.grey[400],
                  ),
                ),
                format: timeFormat,
                onShowPicker: (context, currentValue) async {
                  final TimeOfDay? time = await showTimePicker(
                    context: context,
                    initialTime:
                        TimeOfDay.fromDateTime(currentValue ?? DateTime.now()),
                  );
                  return time == null ? null : DateTimeField.convert(time);
                },
              )),

          const SizedBox(height: 30),

          ///////////////////////////////////////////////// Champ Commentaire
          DelayedAnimation(
            //delay: 5500,
            delay: 0,
            child: TextField(
              decoration: InputDecoration(
                icon: const Icon(Icons.add_comment),
                labelText: 'Commentaire',
                labelStyle: TextStyle(
                  color: Colors.grey[400],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
