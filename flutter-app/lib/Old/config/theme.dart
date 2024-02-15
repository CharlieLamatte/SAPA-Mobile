import 'package:flutter/material.dart';

ThemeData theme(){
  return ThemeData(
    primaryColor: Colors.teal[500],
    primaryColorDark: Colors.teal[900],
    primaryColorLight: Colors.teal[100],

    scaffoldBackgroundColor: Colors.white, colorScheme: const ColorScheme(background: Colors.blueGrey),
  );
}