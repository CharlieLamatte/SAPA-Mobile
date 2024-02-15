import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:datetime_picker_formfield/datetime_picker_formfield.dart';


class rechercheActivite extends StatefulWidget {
  const rechercheActivite({Key? key}) : super(key: key);


  @override
  State<rechercheActivite> createState() => _rechercheActiviteState();
}

class _rechercheActiviteState extends State<rechercheActivite> {
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
            // Scroll view ///////////////////////////////////////////////////////
            body: Center(
                child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(25.0, 0.0, 25.0, 20.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ///////////////////////////////////////////////// Titre
                  Text(
                    "Nouvelle Séance".toUpperCase(),
                    style: const TextStyle(
                      color: Color(0xFF001e1b),
                      fontSize: 26.0,
                    ),
                  ),

                  const Divider(
                    color: Colors.teal,
                    thickness: 1.5,
                    indent: 10,
                    endIndent: 50,
                  ),

                ],
              ),
            ))));
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
          TextField(
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
              
            ),
            
          const SizedBox(height: 30),

          ///////////////////////////////////////////////// Champ date
         TextField(
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

          const SizedBox(height: 30),

          ///////////////////////////////////////////////// Champ durée
         DateTimeField(
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
              ),

          const SizedBox(height: 30),

          ///////////////////////////////////////////////// Champ Commentaire
          TextField(
              decoration: InputDecoration(
                icon: const Icon(Icons.add_comment),
                labelText: 'Commentaire',
                labelStyle: TextStyle(
                  color: Colors.grey[400],
                ),
              ),
            ),
        ],
      ),
    );
  }
}
