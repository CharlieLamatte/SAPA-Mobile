import 'package:flutter/material.dart';

class MonTitreDeCategorie extends StatefulWidget {
  final String myLabelTitle;

  const MonTitreDeCategorie({
    Key? key,
    required this.myLabelTitle,
  }) : super(key: key);

  @override
  _MonTitreDeCategorieState createState() => _MonTitreDeCategorieState();
}

class _MonTitreDeCategorieState extends State<MonTitreDeCategorie> {
  @override
  Widget build(BuildContext context) {
    return
      Container(
        width: 250.0,
        padding: const EdgeInsets.symmetric(
          horizontal: 20.0,
          vertical: 2.0,
        ),
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: Theme.of(context).primaryColor,
          border: Border.all(
              color: Theme.of(context).primaryColor,
              width: 2.0
          ),
          borderRadius: const BorderRadius.all(
              Radius.circular(5.0)
          ),
        ),

        child: RichText(
          text: TextSpan(
            text: widget.myLabelTitle,
            style: TextStyle(
              color: Theme.of(context).splashColor,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
      );
  }
}