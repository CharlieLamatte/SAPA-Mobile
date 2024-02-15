import 'package:flutter/material.dart';

class PhotoDeProfilForm extends StatefulWidget {

  const PhotoDeProfilForm({Key? key}) : super(key: key);

  @override
  _PhotoDeProfilFormState createState() => _PhotoDeProfilFormState();
}

class _PhotoDeProfilFormState extends State<PhotoDeProfilForm> {
  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Scaffold(
        // App bar ///////////////////////////////////////////////////////////
        appBar: AppBar(
            titleSpacing: 0.0,
            elevation: 0,
            backgroundColor: Theme.of(context).scaffoldBackgroundColor,
            leading: IconButton(
              icon: const Icon(Icons.menu_rounded),
              color: Colors.teal,
              onPressed: () => {}, // TODO
            ),
            actions: [
              IconButton(
                icon: const Icon(Icons.power_settings_new_rounded),
                color: Colors.teal,
                onPressed: () => {}, // TODO
              ),
            ]
        ),



        // Scroll view ///////////////////////////////////////////////////////
        body: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(25.0, 0.0, 25.0, 20.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [

                // Titre ///////////////////////////////////////////
                RichText(
                  text: TextSpan(
                    text: "mon corps".toUpperCase(),
                    style: const TextStyle(
                      color: Color(0xFF001e1b),
                      fontSize: 26.0,
                    ),
                  ),
                ),
                const Divider(
                  color: Colors.teal,
                  thickness: 1.5,
                  indent: 10,
                  endIndent: 50,
                ),

                const SizedBox(
                  height: 20.0,
                ),

                // TODO modification de la pdp

              ],
            ),

          ),
        ),

      ),
    );
  }
}
