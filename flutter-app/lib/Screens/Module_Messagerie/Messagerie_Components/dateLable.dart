import 'package:flutter/material.dart';

class DateLable extends StatelessWidget {
  const DateLable({
    Key? key,
    required this.lable,
  }) : super(key: key);

  final String lable;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(
            vertical: 32.0
        ),
        child: Container(
          decoration: BoxDecoration(
            color: Theme.of(context).cardColor,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Padding(
            padding: const EdgeInsets.symmetric(
                vertical: 4.0,
                horizontal: 12,
            ),
            child: Text(
              lable,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: Theme.of(context).primaryColorDark,
              ),
            ),
          ),
        ),
      ),
    );
  }
}
