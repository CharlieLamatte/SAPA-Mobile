import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Components/maNavDrawer.dart';

class MaProgressionScreen extends StatefulWidget {

  const MaProgressionScreen({Key? key,}) : super(key: key);

  @override
  State<StatefulWidget> createState() => MaProgressionScreenState();
}

class MaProgressionScreenState extends State<MaProgressionScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Ma Progression'),
        backgroundColor: const Color(0xFF077276),
        elevation: 0.0,
      ),
      drawer: const MyNavDrawer(),
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
