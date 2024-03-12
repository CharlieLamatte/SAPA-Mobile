import 'dart:convert';
import 'package:flutter_application_3/Screens/Module_ListeSeance/ListeSeance_Utils/evenement.dart';
import 'package:http/http.dart' as http;
import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Components/maNavDrawer.dart';
import 'package:flutter_application_3/Screens/Components/monAppBar.dart';
import 'package:flutter_application_3/Screens/Module_Emargement/Patient.dart';
import 'package:flutter_application_3/Utils/sessionManager.dart';

class Emargement extends StatefulWidget {
  final Evenement seance;

  const Emargement({
    Key? key,
    required this.seance,
  }) : super(key: key);

  @override
  State<Emargement> createState() => _EmargementState();
}

class _EmargementState extends State<Emargement> {
  List<Patient> patients = [];
  Map<Patient, String?> presenceMap = {};

  @override
  void initState() {
    super.initState();
    fetchPatientsFromAPI();
  }

  void fetchPatientsFromAPI() async {
    var url = Uri.parse('http://127.0.0.1:8000/Participants/GetAll');
    Map<String, String> headers = {"Content-type": "application/json"};
    Map<String, dynamic> jsonBody = {
      "username": SessionManager.username,
      "password": SessionManager.password,
      "id_seance": SessionManager.id_user
    };
    String requestBody = json.encode(jsonBody);

    try {
      var response = await http.post(
        url,
        headers: headers,
        body: requestBody,
      );

      final List<dynamic> jsonDataList = json.decode(response.body);

      if (response.statusCode == 200) {
        setState(() {
          patients.clear();
          for (var jsonData in jsonDataList) {
            Patient patient = Patient.fromJson(jsonData);
            patients.add(patient);
          }
        });
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
                'Erreur lors de la communication avec la base de données: ${response.body}'),
            duration: const Duration(seconds: 3),
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Erreur survenue lors de la connexion. $e'),
          duration: const Duration(seconds: 3),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Scaffold(
        appBar: MonAppBar(
          myTitle: "Émargement de la séance",
        ),
        drawer: const MyNavDrawer(),
        body: Container(
          padding: const EdgeInsets.all(25.0),
          child: Column(
            children: [
              Table(
                children: [
                  // première ligne d'en-tête
                  const TableRow(children: [
                    Text("Bénéficiaire",
                        style: TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 15)),
                    Text("Présence",
                        style: TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 15)),
                    Text("Absence",
                        style: TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 15)),
                    Text("Excusé",
                        style: TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 15))
                  ]),

                  for (var patient in patients)
                    TableRow(children: [
                      TableCell(
                        verticalAlignment: TableCellVerticalAlignment.middle,
                        child: Text(
                            "${patient.nomPatient} ${patient.prenomPatient}"),
                      ),
                      RadioListTile(
                        value: "presence",
                        groupValue: presenceMap[patient],
                        onChanged: (value) {
                          setState(() {
                            presenceMap[patient] = value.toString();
                          });
                        },
                      ),
                      RadioListTile(
                        value: "absence",
                        groupValue: presenceMap[patient],
                        onChanged: (value) {
                          setState(() {
                            presenceMap[patient] = value.toString();
                          });
                        },
                      ),
                      CheckboxListTile(
                        controlAffinity: ListTileControlAffinity.leading,
                        value: presenceMap[patient] == null ? null : true,
                        onChanged: (value) {
                          setState(() {
                            if (value == true) {
                              presenceMap[patient] = "excuse";
                            } else {
                              presenceMap[patient] = null;
                            }
                          });
                        },
                      ),
                    ]),
                ],
              ),
              const SizedBox(height: 20.0),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  ElevatedButton(
                    onPressed: () {
                      //mettre le retour à la bonne séance
                    },
                    child: Text('Retour'),
                  ),
                  const SizedBox(width: 40.0),
                  ElevatedButton(
                    onPressed: () {},
                    child: Text('Emarger'),
                  ),
                ],
              ),
              const SizedBox(height: 20.0),
              ElevatedButton(
                onPressed: () {},
                child: Text('Valider la séance'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
