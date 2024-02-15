import 'package:flutter/material.dart';

import 'package:flutter_application_3/old/Archives/SeancesArchives/interface_seance.dart';

void main() => runApp(const MyApp());

class MyApp extends StatefulWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  _MyAppState createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  @override
  Widget build(BuildContext context) {
    return const MaterialApp(
        debugShowCheckedModeBanner: false, home: SeancesHome());
  }
}

///*********************************************************************
///Send data to a new screen :
///https://docs.flutter.dev/cookbook/navigation/passing-data
