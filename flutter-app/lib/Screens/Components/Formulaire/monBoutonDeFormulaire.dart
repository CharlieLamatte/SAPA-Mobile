import 'package:flutter/material.dart';

class MonBoutonDeFormulaire extends StatefulWidget {
  final FormState? maFormState;
  final String monLabel;

  const MonBoutonDeFormulaire({
    Key? key,
    required this.maFormState,
    required this.monLabel
  }) : super(key: key);

  @override
  _MonBoutonDeFormulaireState createState() => _MonBoutonDeFormulaireState();
}

class _MonBoutonDeFormulaireState extends State<MonBoutonDeFormulaire> {
  @override
  Widget build(BuildContext context) {
    return
        ElevatedButton(
            style: ButtonStyle(
                padding: MaterialStateProperty.all<EdgeInsets>(
                    const EdgeInsets.all(0.0)
                ),
                shape: MaterialStateProperty.all<RoundedRectangleBorder>(
                    RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10.0)
                    )
                )
            ),

            onPressed: () => {
              if(widget.maFormState!.validate()) {
                // TODO changer de page ??
              }
            },

            child: Text(
              widget.monLabel.toUpperCase(),
                style: TextStyle(
                color: Theme.of(context).splashColor,
              ),
          ),
        );
  }
}