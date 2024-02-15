import 'package:flutter/material.dart';

class MonChampDeSeance extends StatefulWidget {
  final String myLabel, myData;
  final double? space;

  const MonChampDeSeance({
    Key? key,
    required this.myLabel,
    required this.myData,
    this.space
  }) : super(key: key);



  @override
  _MonChampDeSeanceState createState() => _MonChampDeSeanceState();
}

class _MonChampDeSeanceState extends State<MonChampDeSeance> {
  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          flex: 1,
          child: Container(
            alignment: Alignment.centerLeft,
            child : RichText(
              text: TextSpan(
                text: widget.myLabel,
                style: TextStyle(
                  color: Theme.of(context).focusColor,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ),
        ),
        Expanded(
          flex: 3,
          child: Container(
            padding: const EdgeInsets.fromLTRB(0.0, 0.0, 10.0, 0.0),
            alignment: Alignment.centerRight,
            decoration: BoxDecoration(
              color: Theme.of(context).hoverColor,
              border: Border.all(
                  color: Theme.of(context).hoverColor,
                  width: 2.0
              ),
              borderRadius: const BorderRadius.all(
                  Radius.circular(5.0)
              ),
            ),
            child : TextFormField(
              initialValue: widget.myData,
              enabled: false,
            ),
            ),
          ),

        SizedBox( // TODO ne fonctionne pas :/
          height: widget.space,
        ),
      ],
    );
  }
}
