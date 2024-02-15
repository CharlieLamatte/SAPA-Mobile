import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Components/maNavDrawer.dart';
import 'package:flutter_application_3/Screens/Components/monAppBar.dart';

class MaProgression extends StatefulWidget {
  const MaProgression({
    Key? key
  }) : super(key: key);

  @override
  _MaProgressionState createState() => _MaProgressionState();
}

class _MaProgressionState extends State<MaProgression> {
  @override
  Widget build(BuildContext context) {
    return
      SafeArea(
        child: Scaffold(
          // App bar ///////////////////////////////////////////////////////////
          appBar: MonAppBar(
            myTitle: 'Ma Progression',
          ),
          drawer: const MyNavDrawer(),

          body: const Center(
            child: Padding(
              padding: EdgeInsets.only(
                  left: 25.0,
                  top: 15.0,
                  right: 25.0,
                  bottom: 15.0
              ),
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [

                    Text('Information : \n\n 🔨 Cette pages est en cours de construction ! 🔨 '),

                    // TODO : corps de la page

                    SizedBox(
                      height: 15.0,
                    ),

                    /////////////////////////////////////////////
                    // Bouton ///////////////////////////////////
                    // TODO : bouton
                  ],
                ),
              ),
            ),
          ),
        ),
      );
  }
}