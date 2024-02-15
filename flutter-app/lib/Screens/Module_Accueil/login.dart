import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert' as convert;

class Login {
  ///Check si l'utilisateur existe pour accéder à l'API
  static checkUser(BuildContext context, String utilisateur, String mdp) async {
    print('checkUser launched');
    var url = Uri.http(
        '127.0.0.1:8000', '/login', {'pseudo': utilisateur, 'mdp': mdp});
    print('url parsed: $url');
    // Await the http get response, then decode the json-formatted response.
    var response = await http.post(url);
    print('http post done');
    if (response.statusCode == 200) {
      return true; // Connexion réussie
    } else if (response.statusCode == 403) {
      var jsonResponse =
          convert.jsonDecode(response.body) as Map<String, dynamic>;
      var $errorMessage = jsonResponse['statut'];
      print($errorMessage);
      return $errorMessage; // Renvoie le message d'erreur
    } else {
      print('SAPA marché'); // Message générique en cas d'erreur inattendue
    }
  }
}
