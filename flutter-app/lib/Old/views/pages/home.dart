import 'package:flutter/material.dart';
import 'package:flutter_application_3/old/views/components/category_grid.dart';
import 'package:flutter_application_3/old/views/components/motivation_carousel.dart';
import 'package:flutter_application_3/old/views/components/nav_drawer.dart';
import 'package:flutter_application_3/old/views/components/reminder_home.dart';

class HomePage extends StatefulWidget {
  final Function() updateDarkThemeState;

  const HomePage(this.updateDarkThemeState, {Key? key}) : super(key: key);

  @override
  State<StatefulWidget> createState() => HomePageState(updateDarkThemeState);
}

class HomePageState extends State<HomePage> {
  void Function() updateDarkThemeState;

  HomePageState(this.updateDarkThemeState);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Bienvenue'),
        backgroundColor: const Color(0xFF077276),
        elevation: 0.0,
      ),
      drawer: NavDrawer(updateDarkThemeState),
      body: Container(
        decoration: const BoxDecoration(),
        margin: const EdgeInsets.only(top: 0),
        child: ListView(
          children: [
            /*HeaderText(
              'Prochaine séance:',
            ),*/
            Reminder(updateDarkThemeState),
            /*HeaderText(
              'Bon retour parmi nous $loggedUsername !',
            ),*/
            CategoryGrid(updateDarkThemeState),
            const MotivationCarousel(),
          ],
        ),
      ),
    );
  }
}
