import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Components/Formulaire/monTextFormField.dart';
import 'package:flutter_application_3/Screens/Components/maNavDrawer.dart';
import 'package:flutter_application_3/Screens/Components/monAppBar.dart';
import 'package:flutter_application_3/Screens/Components/monTitreDePage.dart';

import 'monProfil.dart';

class MonCorpsForm extends StatefulWidget {
  final double? taille;
  final double? poids;

  const MonCorpsForm({
    Key? key,
    this.taille,
    this.poids,
  }) : super(key: key);

  @override
  _MonCorpsFormState createState() => _MonCorpsFormState();
}

class _MonCorpsFormState extends State<MonCorpsForm> {

  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();

  final RegExp _tailleRegex = RegExp(r"^[0-1]\.[0-9]{2}$");
  final RegExp _poidsRegex = RegExp(r"^([1-2]?)[0-9]{2}(\.[0-9])?$");

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Scaffold(
        // App bar ///////////////////////////////////////////////////////////
        appBar: MonAppBar(
          myTitle: 'Mon Profil',
        ),
        drawer: const MyNavDrawer(),

        body: Padding(
          padding: const EdgeInsets.only(
              left: 25.0,
              right: 25.0,
              top: 15.0,
              bottom: 15.0
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch, // étire sur tt l'écran
            children: [

              //////////////////////////////////////////////////////////////////
              // Titre /////////////////////////////////////////////////////////
              const MonTitreDePage(
                  myTitle: "mon corps"
              ),

              //////////////////////////////////////////////////////////////////
              // Scroll view ///////////////////////////////////////////////////
              Expanded(
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [

                      ///////////////////////////////////////////////////
                      // Formulaire /////////////////////////////////////
                      Form(
                        key: _formKey,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            /////////////////////////////////////
                            // Taille ///////////////////////////
                            MonTextFormField(
                              myLabelTitle: 'Taille',
                              myVar: widget.taille.toString(),
                              myRegex: _tailleRegex,
                              myValidation: 'Entrez une taille valide en m (ex: 1.23)',
                              myHintText: 'Taille (en m)',
                              mySuffixText: 'm',
                              mySuffixIcon: Icons.straighten_rounded,
                            ),

                            const SizedBox(
                              height: 30.0,
                            ),

                            /////////////////////////////////////
                            // Poids ////////////////////////////
                            MonTextFormField(
                              myLabelTitle: 'Poids',
                              myVar: widget.poids.toString(),
                              myRegex: _poidsRegex,
                              myValidation: 'Entrez un poids valide en kg (ex: 75.5)',
                              myHintText: 'Poids (en kg)',
                              mySuffixText: 'kg',
                              mySuffixIcon: Icons.monitor_weight_rounded,
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(
                height: 15.0,
              ),
              ElevatedButton(
                style: ButtonStyle(
                  shape: MaterialStateProperty.all<RoundedRectangleBorder>(
                      RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10.0)
                      )
                  ),
                ),
                child: Text(
                  'sauvegarder'.toUpperCase(),
                  style: TextStyle(
                    color: Theme.of(context).splashColor,
                  ),
                ),
                onPressed: () => {
                  if(_formKey.currentState!.validate()) {
                    // TODO: Faire changer les valeurs dans profil
                    Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => const MonProfile(),
                        )
                    ),
                  }
                },
              )

              /////////////////////////////////////////////
              // Bouton sauvegarder ///////////////////////
/*
              RaisedButton(
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10.0)
                ),
                color: Theme.of(context).primaryColor,
                elevation: 0,
                padding: const EdgeInsets.symmetric(
                  vertical: 15.0,
                ),
                onPressed: () => {
                  if(_formKey.currentState!.validate()) {
                    Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => const MonProfile(),
                        )
                    ),
                  }
                },
                child: Text(
                  'sauvegarder'.toUpperCase(),
                  style: TextStyle(
                    color: Theme.of(context).splashColor,
                  ),
                ),
              ),
*/
            ],
          ),
        ),
      ),
    );
  }
}
