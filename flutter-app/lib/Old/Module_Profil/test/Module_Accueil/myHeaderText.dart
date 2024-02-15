import 'package:flutter/material.dart';

class MyHeaderText extends StatefulWidget {
  final String text;

  const MyHeaderText({
    Key? key,
    required this.text
  }) : super(key: key);

  @override
  _MyHeaderTextState createState() => _MyHeaderTextState();
}

class _MyHeaderTextState extends State<MyHeaderText> {
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.only(left: 10),
      margin: const EdgeInsets.only(top: 10),
      child: Text(
        widget.text,
        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 20),
        textAlign: TextAlign.left,
      ),
    );
  }
}
