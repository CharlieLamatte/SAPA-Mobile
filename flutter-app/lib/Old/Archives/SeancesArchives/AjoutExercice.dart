import 'package:datetime_picker_formfield/datetime_picker_formfield.dart';
import 'package:flutter/material.dart';
import 'package:flutter_application_3/old/Archives/SeancesArchives/afficheSeance.dart';
import 'package:flutter_application_3/old/delayed_animation.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';

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
      title: 'Ajouter Exercice',
      theme: ThemeData(
        primarySwatch: Colors.teal,
      ),
      home: const ajoutExercice(title: 'Ajouter Exercice'),
    );
  }
}

class ajoutExercice extends StatefulWidget {
  const ajoutExercice({Key? key, required this.title}) : super(key: key);

  final String title;

  @override
  State<ajoutExercice> createState() => _ajoutExerciceState();
}

class _ajoutExerciceState extends State<ajoutExercice> {
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
                        "Ajouter Exercice".toUpperCase(),
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
                        "Veuillez renseigner les données concernant votre exercice.",
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

              const SizedBox(height: 0),

              ///////////////////////////////////////////////// Form
              const ExerciceForm(),

              const SizedBox(height: 30),

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
              const SizedBox(height: 30),

              ///////////////////////////////////////////////// Annuler
              DelayedAnimation(
                //delay: 6500,
                delay: 0,
                child: Expanded(
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
                            builder: (context) => const maSeance(
                              title: 'Ma Seance',
                            ),
                          ),
                        );
                      },
                      child: const Text('Annuler'),
                    ),
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

///////////////////////////////////////////////// Mise en place du formulaire
class ExerciceForm extends StatefulWidget {
  const ExerciceForm({Key? key}) : super(key: key);

  @override
  _ExerciceFormState createState() => _ExerciceFormState();
}

class _ExerciceFormState extends State<ExerciceForm> {
  TextEditingController dateinput = TextEditingController();
  TextEditingController timeinput = TextEditingController();
  TextEditingController textinputD = TextEditingController();
  TextEditingController textinputP = TextEditingController();
  TextEditingController textinputR = TextEditingController();
  final timeFormat = DateFormat("H:mm");
  Duration initialtimer = const Duration();

  var itemsDistance = ['0-50m', '50-100m', '100-150m', '150-200m', '...'];
  var itemsPoidsRep = [
    '0',
    '1',
    '2',
    '3',
    '4',
    '5',
    '6',
    '7',
    '8',
    '9',
    '10',
    '11',
    '12',
    '13',
    '14',
    '15',
    '16',
    '17',
    '18',
    '19',
    '20',
    '21',
    '22',
    '23',
    '24',
    '25',
    '26',
    '27',
    '28',
    '29',
    '...'
  ];
  int _value = 0;

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
          ///////////////////////////////////////////////// Temps
          DelayedAnimation(
              //delay: 4500,
              delay: 0,
              child: DateTimeField(
                decoration: InputDecoration(
                  icon: const Icon(Icons.timer), //icon of text field
                  labelText: 'Temps',
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

          ///////////////////////////////////////////////// Distance
          DelayedAnimation(
              //delay: 3500,
              delay: 0,
              child: TextField(
                controller: textinputD,
                decoration: InputDecoration(
                  icon: const Icon(Icons.directions_run),
                  labelText: 'Distance',
                  labelStyle: TextStyle(
                    color: Colors.grey[400],
                  ),
                  suffixIcon: PopupMenuButton<String>(
                    icon: const Icon(Icons.arrow_drop_down),
                    onSelected: (String value) {
                      textinputD.text = value;
                    },
                    itemBuilder: (BuildContext context) {
                      return itemsDistance
                          .map<PopupMenuItem<String>>((String value) {
                        return PopupMenuItem(
                            value: value,
                            child: Text(value));
                      }).toList();
                    },
                  ),
                ),
                readOnly: true,
              )),

          const SizedBox(height: 30),

          ///////////////////////////////////////////////// Poids
          DelayedAnimation(
              //delay: 3500,
              delay: 0,
              child: TextField(
                controller: textinputP,
                decoration: InputDecoration(
                  icon: const Icon(Icons.fitness_center),
                  labelText: 'Poids',
                  labelStyle: TextStyle(
                    color: Colors.grey[400],
                  ),
                  suffixIcon: PopupMenuButton<String>(
                    icon: const Icon(Icons.arrow_drop_down),
                    onSelected: (String value) {
                      textinputP.text = value;
                    },
                    itemBuilder: (BuildContext context) {
                      return itemsPoidsRep
                          .map<PopupMenuItem<String>>((String value) {
                        return PopupMenuItem(
                            value: value,
                            child: Text(value));
                      }).toList();
                    },
                  ),
                ),
                readOnly: true,
              )),

          const SizedBox(height: 30),

          ///////////////////////////////////////////////// Répétition
          DelayedAnimation(
              //delay: 3500,
              delay: 0,
              child: TextField(
                controller: textinputR,
                decoration: InputDecoration(
                  icon: const Icon(Icons.change_circle_outlined),
                  labelText: 'Répétitions',
                  labelStyle: TextStyle(
                    color: Colors.grey[400],
                  ),
                  suffixIcon: PopupMenuButton<String>(
                    icon: const Icon(Icons.arrow_drop_down),
                    onSelected: (String value) {
                      textinputR.text = value;
                    },
                    itemBuilder: (BuildContext context) {
                      return itemsPoidsRep
                          .map<PopupMenuItem<String>>((String value) {
                        return PopupMenuItem(
                            value: value,
                            child: Text(value));
                      }).toList();
                    },
                  ),
                ),
                readOnly: true,
              )),

          const SizedBox(height: 30),

          ///////////////////////////////////////////////// Ressenti
          DelayedAnimation(
            //delay: 5500,
            delay: 0,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: <Widget>[
                GestureDetector(
                  onTap: () => setState(() => _value = 0),
                  child: Container(
                      height: 56,
                      width: 56,
                      color:
                          _value == 0 ? Colors.grey[350] : Colors.transparent,
                      child: const Icon(
                        Icons.sentiment_very_dissatisfied,
                        color: Colors.red,
                      )),
                ),
                const SizedBox(width: 4),
                GestureDetector(
                  onTap: () => setState(() => _value = 1),
                  child: Container(
                    height: 56,
                    width: 56,
                    color: _value == 1 ? Colors.grey[350] : Colors.transparent,
                    child: const Icon(
                      Icons.sentiment_neutral,
                      color: Colors.orange,
                    ),
                  ),
                ),
                GestureDetector(
                  onTap: () => setState(() => _value = 2),
                  child: Container(
                    height: 56,
                    width: 56,
                    color: _value == 2 ? Colors.grey[350] : Colors.transparent,
                    child: const Icon(
                      Icons.sentiment_satisfied_alt,
                      color: Colors.yellow,
                    ),
                  ),
                ),
                const SizedBox(width: 4),
                GestureDetector(
                  onTap: () => setState(() => _value = 3),
                  child: Container(
                    height: 56,
                    width: 56,
                    color: _value == 3 ? Colors.grey[350] : Colors.transparent,
                    child: const Icon(
                      Icons.mood,
                      color: Colors.green,
                    ),
                  ),
                ),
              ],
            ),
          )
        ],
      ),
    );
  }
}
