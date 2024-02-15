import 'package:flutter/cupertino.dart';

class HeaderText extends StatelessWidget {
  final String text;

  const HeaderText({
    Key? key,
    required this.text
  }): super(key: key);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.only(left: 10),
      margin: const EdgeInsets.only(top: 10),
      child: Text(
        text,
        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 20),
        textAlign: TextAlign.left,
      ),
    );
  }
}
