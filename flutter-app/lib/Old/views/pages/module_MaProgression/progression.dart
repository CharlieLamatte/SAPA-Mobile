import 'package:flutter/material.dart';
import 'package:flutter_application_3/old/views/components/nav_drawer.dart';

class ProgressPage extends StatefulWidget {
  final Function() updateDarkThemeState;

  const ProgressPage(this.updateDarkThemeState, {Key? key}) : super(key: key);

  @override
  State<StatefulWidget> createState() =>
      ProgressPageState(updateDarkThemeState);
}

class ProgressPageState extends State<ProgressPage> {
  void Function() updateDarkThemeState;

  ProgressPageState(this.updateDarkThemeState);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Ma Progression'),
        backgroundColor: const Color(0xFF077276),
        elevation: 0.0,
      ),
      drawer: NavDrawer(updateDarkThemeState),
      //backgroundColor: Colors.grey[400],
      body: Container(
        decoration: const BoxDecoration(),
        margin: const EdgeInsets.only(top: 0),
        child: ListView(
          children: const [
            // MotivationCarousel(),
            /*HeaderText(
              'Vous avez bien progressé $loggedUsername !',
            ),*/
            //CategoryGrid(),
            /*HeaderText(
              'blblabla',
            ),*/
            //Reminder(),
          ],
        ),
      ),
    );
  }
}
