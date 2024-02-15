import 'package:flutter/material.dart';

class Formulaire extends StatefulWidget {
  const Formulaire({
    Key? key
  }) : super(key: key);

  @override
  _FormulaireState createState() => _FormulaireState();
}

class _FormulaireState extends State<Formulaire> {
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();

  @override
  Widget build(BuildContext context) {
    return
      ///////////////////////////////////////////////////
      // Formulaire /////////////////////////////////////
      Form(
        key: _formKey,
        child: const Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [

            // TODO : champs du formulaire

            SizedBox(
              height: 30.0,
            ),
          ],
        ),
      );
  }
}
