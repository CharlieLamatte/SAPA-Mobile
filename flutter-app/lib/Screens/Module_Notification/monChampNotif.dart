import 'package:flutter/material.dart';

class MonChampNotif extends StatefulWidget {
  final String myLabel, myData;
  final double? space;

  const MonChampNotif({
    Key? key,
    required this.myLabel,
    required this.myData,
    this.space
  }) : super(key: key);

  @override
  MonChampNotifState createState() => MonChampNotifState();
}

class MonChampNotifState extends State<MonChampNotif> {
  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          flex: 1,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.start,
            crossAxisAlignment: CrossAxisAlignment.start,
          children:<Widget>[
            Container(
              //padding: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 0.0),
              padding: const EdgeInsets.all(3.0),
              //alignment: Alignment.topLeft,
              decoration: BoxDecoration(
                color: const Color.fromRGBO(136, 198, 76, 1),
                border: Border.all(
                    color: Theme.of(context).focusColor,
                    width: 1.0
                ),
                borderRadius: const BorderRadius.all(
                    Radius.circular(5.0)
                ),
              ),
              child : RichText(
                text: TextSpan(
                  text: widget.myLabel,
                  style: const TextStyle(
                    color: Colors.white,
                  ),
                ),
              ),
            ),
            Container(
              //padding: const EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 0.0),
              padding: const EdgeInsets.all(3.0),
              alignment: Alignment.centerLeft,
              decoration: BoxDecoration(
                color: Theme.of(context).hoverColor,
                border: Border.all(
                    color: Theme.of(context).focusColor,
                    width: 1.0
                ),
                borderRadius: const BorderRadius.all(
                    Radius.circular(5.0)
                ),
              ),
              child : RichText(
                text: TextSpan(
                  text: widget.myData,
                  style: TextStyle(
                    color: Theme.of(context).focusColor,
                  ),
                ),
              ),
            ),
            ],
          ),
        ),

        SizedBox( // TODO ne fonctionne pas :/
          height: widget.space,
        ),
      ],
    );
  }
}
