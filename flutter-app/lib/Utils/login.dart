// ignore_for_file: use_build_context_synchronously, prefer_const_constructors

import 'package:flutter_application_3/Screens/Module_Accueil_Intervenant/myHome.dart';
import 'package:flutter_application_3/Utils/sessionManager.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:flutter/material.dart';

class Login {
  static String hostAdress = '127.0.0.1:8000';

  static loginUser(
      BuildContext context, String username, String password) async {
    var url = Uri.parse('http://$hostAdress/SAPA-Mobile/login');
    Map<String, String> headers = {"Content-type": "application/json"};
    Map<String, String> jsonBody = {"username": username, "password": password};
    String requestBody = json.encode(jsonBody);

    try {
      var response = await http.post(
        url,
        headers: headers,
        body: requestBody,
      );

      final jsonResponse = json.decode(response.body);

      if (response.statusCode == 200) {
        SessionManager.username = jsonResponse['identifiant'];
        SessionManager.password = password;
        SessionManager.isConnected = true;
        SessionManager.id_user = jsonResponse['id_user'];
        SessionManager.roles = jsonResponse['roles'];
        SessionManager.est_coordinateur_peps =
            jsonResponse['est_coordinateur_peps'];
        SessionManager.compteur = jsonResponse['compteur'];
        if (jsonResponse.containsKey('fonction') &&
            jsonResponse['fonction'] != null) {
          SessionManager.fonction = jsonResponse['fonction'];
        }

        Navigator.push(
          context,
          MaterialPageRoute(builder: (context) => const MyHomePage()),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur: ${jsonResponse['status']}'),
            duration: const Duration(seconds: 3),
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Erreur lors de la connexion. $e'),
          duration: const Duration(seconds: 3),
        ),
      );
    }
  }
}
