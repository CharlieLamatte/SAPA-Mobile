import 'package:flutter/material.dart';
import 'package:adaptive_theme/adaptive_theme.dart';
import 'package:flutter_application_3/Screens/Module_Accueil/myLoginPage.dart';
import 'package:flutter_application_3/Screens/Module_Profil/monProfil.dart';
import 'package:flutter_application_3/Screens/Module_Notification/notifications.dart';
import '../Module_Accueil_Intervenant/myHome.dart';

class MyNavDrawer extends StatefulWidget {
  const MyNavDrawer({
    Key? key,
  }) : super(key: key);

  @override
  _MyNavDrawerState createState() => _MyNavDrawerState();
}

class _MyNavDrawerState extends State<MyNavDrawer> {
  bool darkMode = false;
  dynamic savedThemeMode;
  late Icon icon;

  @override
  void initState() {
    super.initState();
    getCurrentTheme();
  }

  // savoir quel thème on a actuellement
  Future getCurrentTheme() async {
    savedThemeMode = await AdaptiveTheme.getThemeMode();
    if (savedThemeMode.toString() == 'AdaptiveThemeMode.dark') {
      setState(() {
        darkMode = true;
        icon = Icon(
          Icons.dark_mode_rounded,
          color: Theme.of(context).primaryColor,
        );
      });
    }
    else {
      setState(() {
        darkMode = false;
        icon = Icon(
          Icons.light_mode_rounded,
          color: Theme.of(context).primaryColor,
        );
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Drawer(
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          const DrawerHeader(
            child: null,
            decoration: BoxDecoration(
              image: DecorationImage(
                  image: AssetImage("assets/logo_ss86.png"),
                  fit: BoxFit.contain
              ),
            ),
          ),

          // Mon Profil ///////////////////////////////////////////////////////
          ListTile(
            leading: Icon(
              Icons.account_circle,
              color: Theme.of(context).primaryColor,
            ),
            title: const Text('Mon Profil'),
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => const MonProfile(),
              ),
            ),
          ),

          // Theme sombre //////////////////////////////////////////////////////
          SwitchListTile(
            title: const Text('Thème sombre'),
            value: darkMode,
            activeColor: Theme.of(context).primaryColor,
            onChanged: (bool value) {
              print(value);
              if (value == true) {
                AdaptiveTheme.of(context).setDark();
                icon = Icon(
                  Icons.dark_mode_rounded,
                  color: Theme.of(context).primaryColor,
                );
              }
              else {
                AdaptiveTheme.of(context).setLight();
                icon = Icon(
                  Icons.light_mode_rounded,
                  color: Theme.of(context).primaryColor,
                );
              }
              setState(() {
                darkMode = value;
              });
            },
            secondary: icon,
          ),

          // Paramètres ////////////////////////////////////////////////////////
          ListTile(
            leading: Icon(
              Icons.settings,
              color: Theme.of(context).primaryColor,
            ),
            title: const Text('Paramètres'),
            onTap: () => // TODO myNavDrawer => Paramètres
            // START : pop-up de construction //
            showDialog(
                context: context,
                builder: (BuildContext _) {
                  return const AlertDialog(
                    title: Text('🔨 Information'),
                    content: Text('Cette page est en cours de construction !'),
                  );
                }
            ),
            // END : pop-up de construction //
          ),

          // Intervenant ///////////////////////////////////////////////////////
          ListTile(
            leading: Icon(
              Icons.account_circle,
              color: Theme.of(context).primaryColor,
            ),
            title: const Text('Intervenant'),
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => const MyHomePage(),
              ),
            ),
          ),

          ListTile(
            leading: Icon(
              Icons.settings,
              color: Theme.of(context).primaryColor,
            ),
            title: const Text("Notifications"),
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => const Notifications(),
              )
            )
          ),

          // Deconnexion ///////////////////////////////////////////////////////
          ListTile(
              leading: Icon(
                Icons.logout,
                color: Theme.of(context).primaryColor,
              ),
              title: const Text('Deconnexion'),
              onTap: () => {
                Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(
                        builder: (context) => const MyLoginPage()
                    ),
                        (route) => false
                ),
              }
            // START : pop-up de construction //
            // showDialog(
            //     context: context,
            //     builder: (BuildContext _) {
            //       return const AlertDialog(
            //         title: Text('🔨 Information'),
            //         content: Text('Cette page est en cours de construction !'),
            //       );
            //     }
            // ),
            // END : pop-up de construction //
          ),
        ],
      ),
    );
  }
}
