import 'package:flutter/material.dart';

class ModifChampDeSeance extends StatefulWidget {
  final String myLabel, myData;
  final bool is_editabled;
  final double? space;

  const ModifChampDeSeance({
    Key? key,
    required this.myLabel,
    required this.myData,
    this.is_editabled = false,
    this.space
  }) : super(key: key);

  @override
  _ModifChampDeSeanceState createState() => _ModifChampDeSeanceState();
}

class _ModifChampDeSeanceState extends State<ModifChampDeSeance> {
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
              color: switchColor(), ////LAAAAAAAA pour modifier la couleur à l'intérieur
              border: Border.all(
                  color: switchColor(), ////LAAAAAAAAAAAAAA pour modifier les contours
                  width: 2.0
              ),
              borderRadius: const BorderRadius.all(
                  Radius.circular(5.0)
              ),
            ),
            child : TextFormField(initialValue: widget.myData, enabled: widget.is_editabled,
            ),
            ),
          ),

        SizedBox( // TODO ne fonctionne pas :/
          height: widget.space,
        ),
      ],
    );
  }


  Color switchColor() {
    if (widget.is_editabled) {
      return Theme.of(context).disabledColor;
    } else {
      return Theme.of(context).hoverColor;
    }
  }



}
