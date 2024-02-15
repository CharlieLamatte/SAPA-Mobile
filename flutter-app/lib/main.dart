import 'package:adaptive_theme/adaptive_theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Module_Accueil/myLoginPage.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

void main() async {
  runApp(MyApp());
}

// palette de couleurs
Map<int, Color> color ={
  50:const Color(0xFFe6f1f1),
  100:const Color(0xFFcde3e4),
  150:const Color(0xFFb5d5d6),
  200:const Color(0xFF9cc7c8),
  250:const Color(0xFF83b9bb),
  300:const Color(0xFF6aaaad),
  350:const Color(0xFF519c9f),
  400:const Color(0xFF398e91),
  450:const Color(0xFF208084),
  500:const Color(0xFF077276),
  550:const Color(0xFF06676a),
  600:const Color(0xFF065b5e),
  650:const Color(0xFF055053),
  700:const Color(0xFF044447),
  750:const Color(0xFF04393b),
  800:const Color(0xFF032e2f),
  850:const Color(0xFF022223),
  900:const Color(0xFF011718),
};

class MyApp extends StatelessWidget {
  MyApp({Key? key}) : super(key: key);

  MaterialColor colorCustom = MaterialColor(0xFF077276, color);
  bool darkMode = true;

  @override
  Widget build(BuildContext context) {
    return AdaptiveTheme(

      // Theme clair ///////////////////////////////////////////////////////////
      light: ThemeData(
        brightness: Brightness.light,
        primarySwatch: colorCustom,
        primaryColor: const Color(0xFF077276),      // couleur de base
        primaryColorLight: const Color(0xFFcde3e4), // bleu très clair
        hoverColor: const Color(0xFFD6D6D6),        // gris très clair
        disabledColor: const Color(0xD0ECEBEB),
        focusColor: Colors.black54,
        splashColor: const Color(0xFFFFFFFF),
        highlightColor: Colors.white,
        indicatorColor: const Color(0xFF8bc63f),
        // TODO implémenter plus des couleurs si nécessaire !!
      ),

      // Theme sombre //////////////////////////////////////////////////////////
      dark: ThemeData(
        brightness: Brightness.dark,
        primarySwatch: colorCustom,
        primaryColor: const Color(0xFF077276),      // couleur de base
        primaryColorLight: const Color(0xFFcde3e4), // bleu très clair
        hoverColor: const Color(0xFFD6D6D6),        // gris très clair
        disabledColor: const Color(0xD0ECEBEB),
        focusColor: Colors.white,
        splashColor: const Color(0xFFFFFFFF),
        highlightColor: Colors.black54,
        // TODO implémenter plus des couleurs si nécessaires !!
      ),

      // Theme initial /////////////////////////////////////////////////////////
      initial: AdaptiveThemeMode.light,

      // Builder ///////////////////////////////////////////////////////////////
      builder: (theme, darkTheme) => MaterialApp(
        debugShowCheckedModeBanner: false,
        title: 'Mon profil',
        theme: theme,
        darkTheme: darkTheme,

        ////////////////////////////////////////////////////
        // gestion des langues /////////////////////////////
        localizationsDelegates: const [
          GlobalMaterialLocalizations.delegate
        ],
        supportedLocales: const [
          Locale('en'),
          Locale('fr')
        ],


        home: const MyLoginPage(),
      ),
    );
  }
}
