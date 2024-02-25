import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../Utils/login.dart';

class MyLoginPage extends StatefulWidget {
  const MyLoginPage({Key? key}) : super(key: key);

  @override
  State<StatefulWidget> createState() => MyLoginPageState();
}

class MyLoginPageState extends State<MyLoginPage> {
  late String utilisateur;
  late String mdp;
  final TextEditingController _passwordController = TextEditingController();
  bool _isSecret = true; // cacher le mdp

  final _formKey = GlobalKey<FormState>();

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Scaffold(
        // App bar ///////////////////////////////////////////////////////////
        appBar: AppBar(
          title: const Text('Connexion'),
          backgroundColor: Theme.of(context).primaryColor,
        ),

        body: Padding(
          padding: const EdgeInsets.only(
              left: 25.0, top: 15.0, right: 25.0, bottom: 15.0),
          child: Column(
              crossAxisAlignment:
                  CrossAxisAlignment.stretch, // étire sur tt l'écran
              children: [
                Expanded(
                  child: CustomScrollView(slivers: [
                    SliverFillRemaining(
                      hasScrollBody: false,
                      child: Column(
                        // crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          //////////////////////////////////////////////////////////////
                          // Logo SAPA /////////////////////////////////////////////////
                          const Expanded(
                            flex: 2,
                            child: Center(
                              child: Image(
                                image: AssetImage("assets/logo_ss86.png"),
                              ),
                            ),
                          ),

                          //////////////////////////////////////////////////////////////
                          // Formulaire de connexion ///////////////////////////////////
                          Expanded(
                            flex: 5,
                            child: Form(
                              key: _formKey,
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const SizedBox(
                                    height: 15.0,
                                  ),

                                  //////////////////////////////////////////////////////
                                  // Identifiant ///////////////////////////////////////
                                  TextFormField(
                                    validator: (String? value) {
                                      if (value == null || value.isEmpty) {
                                        return 'Renseignez le nom d\'utilisateur';
                                      }
                                      utilisateur = value;
                                      return null;
                                    },
                                    decoration: InputDecoration(
                                      hintText: 'Identifiant...',
                                      contentPadding:
                                          const EdgeInsets.symmetric(
                                              horizontal: 10.0),
                                      border: OutlineInputBorder(
                                        borderRadius:
                                            BorderRadius.circular(5.0),
                                        borderSide: BorderSide(
                                          color: Theme.of(context).focusColor,
                                        ),
                                      ),
                                    ),
                                  ),

                                  const SizedBox(
                                    height: 15.0,
                                  ),

                                  //////////////////////////////////////////////////////
                                  // Mot de passe //////////////////////////////////////
                                  TextFormField(
                                    controller: _passwordController,
                                    obscureText: _isSecret,
                                    validator: (String? value) {
                                      if (value == null || value.isEmpty) {
                                        return 'Renseignez le mot de passe.';
                                      }
                                      return null;
                                    },
                                    decoration: InputDecoration(
                                      hintText: 'Mot de passe...',
                                      contentPadding:
                                          const EdgeInsets.symmetric(
                                              horizontal: 10.0),
                                      border: OutlineInputBorder(
                                        borderRadius:
                                            BorderRadius.circular(5.0),
                                        borderSide: BorderSide(
                                          color: Theme.of(context).focusColor,
                                        ),
                                      ),
                                      suffixIcon: InkWell(
                                        onTap: () => setState(
                                            () => _isSecret = !_isSecret),
                                        child: Icon(!_isSecret
                                            ? Icons.visibility
                                            : Icons.visibility_off),
                                      ),
                                    ),
                                  ),

                                  const SizedBox(
                                    height: 15.0,
                                  ),

                                  ////////////////////////////////////////////
                                  // Bouton //////////////////////////////////
                                  Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.stretch,
                                      children: [
                                        ElevatedButton(
                                          style: ElevatedButton.styleFrom(
                                            // foregroundColor: Theme.of(context).primaryColor,
                                            // backgroundColor: Theme.of(context).backgroundColor,
                                            minimumSize: const Size(0, 50),
                                          ),
                                          onPressed: () async {
                                            if (_formKey.currentState!
                                                .validate()) {
                                              Login.loginUser(
                                                  context,
                                                  utilisateur,
                                                  _passwordController.text);
                                            }
                                          },
                                          child: const Text(
                                            "Se connecter",
                                            style: TextStyle(fontSize: 20),
                                          ),
                                        ),
                                      ]),

                                  const SizedBox(
                                    height: 15.0,
                                  ),
                                ],
                              ),
                            ),
                          ),

                          ////////////////////////////////////////////////////////
                          // Texte de consentement ///////////////////////////////
                          Expanded(
                            flex: 1,
                            child: Column(
                                mainAxisAlignment: MainAxisAlignment.end,
                                children: [
                                  RichText(
                                    textAlign: TextAlign.center,
                                    text: TextSpan(
                                      text:
                                          "En vous connectant, vous acceptez d'échanger dans un cadre de respect et vous reconnaissez avoir lu ",
                                      style: TextStyle(
                                        color: Theme.of(context).focusColor,
                                      ),
                                      children: [
                                        TextSpan(
                                          text: 'nos mentions légales.',
                                          recognizer: TapGestureRecognizer()
                                            ..onTap = () => launch(
                                                'https://sportsante86.fr/mentions-legales/'),
                                          style: TextStyle(
                                            color:
                                                Theme.of(context).primaryColor,
                                            decoration:
                                                TextDecoration.underline,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ]),
                          ),
                        ],
                      ),
                    ),
                  ]),
                ),
              ]),
        ),
      ),
    );
  }
}
