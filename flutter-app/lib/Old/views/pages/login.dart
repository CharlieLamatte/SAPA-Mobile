import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Module_Accueil/myHome.dart';
import 'package:flutter_application_3/Utils/global.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:video_player/video_player.dart';

class MyLoginPage extends StatefulWidget {
  const MyLoginPage({
    Key? key
  }) : super(key: key);

  @override
  State<StatefulWidget> createState() => MyLoginPageState();
}

class MyLoginPageState extends State<MyLoginPage> {
  late VideoPlayerController _controller;

  bool _isSecret = true; // cacher le mdp

  final _formKey = GlobalKey<FormState>();

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      home: Scaffold(
        resizeToAvoidBottomInset: false,
        appBar: AppBar(
          title: const Text('Connexion'),
          backgroundColor: const Color(0xFF077276),
        ),
        body: Container(
          decoration: const BoxDecoration(
            image: DecorationImage(
              image: AssetImage("assets/imagelogin.jpg"),
              fit: BoxFit.cover,
            ),
          ),
          padding:
          const EdgeInsets.only(left: 15, right: 15, top: 35, bottom: 35),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Center(
                child: Image(
                  image: AssetImage("assets/logo_ss86.png"),
                ),
              ),
              Form(
                key: _formKey,
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Container(
                        margin: const EdgeInsets.only(
                          bottom: 5,
                          top: 60,
                        ),
                      ),

                      ////////////////////////////////////////////////////////
                      // Identifiant /////////////////////////////////////////
                      TextFormField(
                        validator: (value) => validateUsername(value),
                        decoration: const InputDecoration(
                            hintText: 'Identifiant...',
                            hintStyle:
                            TextStyle(color: Colors.white, fontSize: 18),
                            enabledBorder: OutlineInputBorder(
                              borderSide:
                              BorderSide(color: Colors.white, width: 2.0),
                            )),
                        style: const TextStyle(color: Colors.white),
                      ),
                      Container(
                        margin: const EdgeInsets.only(bottom: 5, top: 20),
                      ),

                      ////////////////////////////////////////////////////////
                      // Mot de passe ////////////////////////////////////////
                      TextFormField(
                        validator: (value) => validatePassword(value),
                        obscureText: _isSecret,
                        decoration: InputDecoration(
                          iconColor: const Color(0xFF077276),
                          suffixIcon: InkWell(
                            onTap: () =>
                                setState(() => _isSecret = !_isSecret),
                            child: Icon(!_isSecret
                                ? Icons.visibility
                                : Icons.visibility_off),
                          ),
                          hintText: 'Mot de passe...',
                          hintStyle: const TextStyle(
                              color: Colors.white, fontSize: 18),
                          enabledBorder: const OutlineInputBorder(
                            borderSide: BorderSide(
                              color: Colors.white,
                              width: 2.0,
                            ),
                          ),
                        ),
                        style: const TextStyle(
                          color: Colors.white,
                        ),
                      ),

                      ////////////////////////////////////////////////////////
                      // Bouton //////////////////////////////////////////////
                      Container(
                        margin: const EdgeInsets.only(
                            top: 50,
                            bottom: 150
                        ),
                        child: ElevatedButton(
                            style: TextButton.styleFrom(
                              foregroundColor: Colors.white, backgroundColor: const Color(0xFF077276),
                            ),
                            onPressed: onLogin,
                            child: const Text(
                              'Connexion',
                              style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 20
                              ),
                            )
                        ),
                      ),

                      ////////////////////////////////////////////////////////
                      // Note en bas de page /////////////////////////////////
                      RichText(
                        text: TextSpan(
                          text:
                          "En vous connectant, vous acceptez d'échanger dans un cadre de respect et vous reconnaissez avoir lu ",
                          style: const TextStyle(color: Colors.white),
                          children: [
                            TextSpan(
                              text: 'nos mentions légales.',
                              recognizer: TapGestureRecognizer()
                                ..onTap = () => launch(
                                    'https://sportsante86.fr/mentions-legales/'
                                ),
                              style: const TextStyle(
                                color: Colors.cyanAccent,
                                decoration: TextDecoration.underline,
                              ),
                            ),
                            const TextSpan(
                                text: ''
                            ),
                          ],
                        ),
                      ),
                    ]
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

//Fontction pour contraindre l'id
  dynamic validateUsername(var value) {
    if (value == null || value.isEmpty) {
      return ('L\'identifiant ne peut être vide');
    }
    loggedUsername = value;
    return null;
  }

//Fontction pour contraindre le mdp
  dynamic validatePassword(var value) {
    if (value == null || value.isEmpty) {
      return "Le mot de passe ne peut être vide";
    } else if (value.length < 8) {
      return 'Le mot de passe doit contenir au moins 8 caractères';
    } else if (!containUppercase(value)) {
      return 'Le mot de passe doit contenir au moins une majuscule';
    }
    return null;
  }

  //Fonction pour vérifier au moins 1 majuscule dans le mdp
  bool containUppercase(value) {
    bool containUppercase = false;
    for (var i = 0; i < value.length; i++) {
      if (value[i] == value[i].toUpperCase()) {
        containUppercase = true;
        break;
      }
    }
    return containUppercase;
  }

  //Bouton Login se connecter
  void onLogin() {
    if (_formKey.currentState!.validate()) {
      Navigator.pop(context);
      Navigator.push(
        context,
        MaterialPageRoute(builder: (context) => const MyHomePage()),
      );
    }
  }
}
