import 'package:flutter/material.dart';
import 'package:flutter_application_3/old/views/pages/home.dart';
import 'package:flutter_application_3/old/views/pages/login.dart';
import 'package:flutter_application_3/old/views/pages/module_MaProgression/progression.dart';

class NavDrawer extends StatelessWidget {
  final Function() updateDarkThemeState;

  const NavDrawer(this.updateDarkThemeState, {Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Drawer(
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          const DrawerHeader(
            decoration: BoxDecoration(
                image: DecorationImage(
                    image: AssetImage("assets/logo_ss86.png"),
                    fit: BoxFit.contain)),
            child: Text(' '),
          ),
          ListTile(
            leading: Icon(
              Icons.home,
              color: Theme.of(context).primaryColor,
            ),
            title: const Text('Accueil'),
            onTap: () => Navigator.push(
                context,
                MaterialPageRoute(
                    builder: (context) => HomePage(updateDarkThemeState))),
          ),
          ListTile(
            leading: Icon(
              Icons.account_circle,
              color: Theme.of(context).primaryColor,
            ),
            title: const Text('Mon Profil'),
            onTap: () => Navigator.push(
                context,
                MaterialPageRoute(
                    builder: (context) => ProgressPage(updateDarkThemeState))),
          ),
          ListTile(
            leading: Icon(
              Icons.dark_mode_sharp,
              color: Theme.of(context).primaryColor,
            ),
            title: const Text('Thème Sombre'),
            onTap: () {
              updateDarkThemeState();
            },
          ),
          ListTile(
            leading: Icon(
              Icons.settings,
              color: Theme.of(context).primaryColor,
            ),
            title: const Text('Paramètres'),
            onTap: () => showDialog(
                context: context,
                builder: (BuildContext _) {
                  return const AlertDialog(
                    title: Text('🔨 Information '),
                    content: Text(
                      'Les paramètres sont en construction !',
                    ),
                  );
                }),
          ),
          ListTile(
              leading: Icon(
                Icons.logout,
                color: Theme.of(context).primaryColor,
              ),
              title: const Text('Deconnexion'),
              onTap: () {
                Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(
                        builder: (context) => const MyLoginPage()),
                    (route) => false);
              }),
        ],
      ),
    );
  }
}
