import 'package:flutter/material.dart';

class MonTitreDePage extends StatefulWidget {
  final String myTitle;

  const MonTitreDePage({
    Key? key,
    required this.myTitle,
  }) : super(key: key);

  @override
  MonTitreDePageState createState() => MonTitreDePageState();
}

class MonTitreDePageState extends State<MonTitreDePage>{

  @override
  Widget build(BuildContext context) {
    return
      Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          RichText(
            text: TextSpan(
              text: widget.myTitle.toUpperCase(),
              style: TextStyle(
                color: Theme.of(context).focusColor,
                fontSize: 26.0,
              ),
            ),
          ),

          Divider(
            color: Theme.of(context).primaryColor,
            thickness: 1.5,
            indent: 10,
            endIndent: 50,
          ),

          const SizedBox(
            height: 20.0,
          ),
        ],
      );
  }
}