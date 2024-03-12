// ignore_for_file: non_constant_identifier_names

class SessionManager {
  static String _username = '';
  static String _password = '';
  static bool _isConnected = false;
  static int _id_user = 0;
  static List<dynamic> _roles = [];
  static bool _est_coordinateur_peps = false;
  static int _compteur = 0;
  static String _fonction = '';

  static String get username => _username;

  static set username(String value) {
    _username = value;
  }

  static String get password => _password;

  static set password(String value) {
    _password = value;
  }

  static bool get isConnected => _isConnected;

  static set isConnected(bool value) {
    _isConnected = value;
  }

  static int get id_user => _id_user;

  static set id_user(int value) {
    _id_user = value;
  }

  static List<dynamic> get roles => _roles;

  static set roles(List<dynamic> value) {
    _roles = value;
  }

  static bool get est_coordinateur_peps => _est_coordinateur_peps;

  static set est_coordinateur_peps(bool value) {
    _est_coordinateur_peps = value;
  }

  static int get compteur => _compteur;

  static set compteur(int value) {
    _compteur = value;
  }

  static String get fonction => _fonction;

  static set fonction(String value) {
    _fonction = value;
  }

  static deconnect() {
    _username = '';
    _password = '';
    _isConnected = false;
    _id_user = 0;
    _roles = [];
    _est_coordinateur_peps = false;
    _compteur = 0;
    _fonction = '';
  }
}
