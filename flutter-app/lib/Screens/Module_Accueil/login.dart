import 'package:http/http.dart' as http;

class Login {
  static Future<Map<String, dynamic>> loginUser(
      String username, String password) async {
    final response =
        await http.get(Uri.parse('127.0.0.1:8000/login/$username/$password'));

    if (response.statusCode == 200) {
      return {'success': true};
    } else if (response.statusCode == 401) {
      return {'error': 'Mot de passe ou email invalide.'};
    } else if (response.statusCode == 403) {
      return {'error': 'Le compte a été désactivé.'};
    } else {
      return {'error': 'Une erreur est survenue.'};
    }
  }
}
