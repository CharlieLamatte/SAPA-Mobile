import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Components/monAppBar.dart';
import 'package:intl/date_symbol_data_local.dart';

//import 'package:flutter_localizations/flutter_localizations.dart';
import 'events.dart';

void main() async {
  initializeDateFormatting().then((_) => runApp(MyApp()));
}

Map<int, Color> color = {
  50: const Color(0xFFe6f1f1),
  100: const Color(0xFFcde3e4),
  150: const Color(0xFFb5d5d6),
  200: const Color(0xFF9cc7c8),
  250: const Color(0xFF83b9bb),
  300: const Color(0xFF6aaaad),
  350: const Color(0xFF519c9f),
  400: const Color(0xFF398e91),
  450: const Color(0xFF208084),
  500: const Color(0xFF077276),
  550: const Color(0xFF06676a),
  600: const Color(0xFF065b5e),
  650: const Color(0xFF055053),
  700: const Color(0xFF044447),
  750: const Color(0xFF04393b),
  800: const Color(0xFF032e2f),
  850: const Color(0xFF022223),
  900: const Color(0xFF011718),
};

class MyApp extends StatelessWidget {
  MyApp({Key? key}) : super(key: key);

  MaterialColor colorCustom = MaterialColor(0xFF077276, color);

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Suivi PEPS',
      theme: ThemeData(
        primarySwatch: colorCustom,
      ),

      // gestion des langues /////////////////////////////
      //localizationsDelegates: const [GlobalMaterialLocalizations.delegate],
      //supportedLocales: const [Locale('en'), Locale('fr')],
      home: const StartPage(),
    );
  }
}

class StartPage extends StatefulWidget {
  const StartPage({Key? key}) : super(key: key);

  @override
  _StartPageState createState() => _StartPageState();
}

class _StartPageState extends State<StartPage> {
  //const MyApp({Key? key, required String title}) : super(key: key);

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: MonAppBar(myTitle: 'Suivi PEPS'),
        body: ElevatedButton(
          child: const Text('Events'),
          onPressed: () => Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const SuiviPepsCalendrier()),
          ),
        ),
      );
}
