import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_application_3/old/Archives/SeancesArchives/afficheSeance.dart';

class SeancesHome extends StatefulWidget {
  const SeancesHome({Key? key}) : super(key: key);

  @override
  _SeancesHomeState createState() => _SeancesHomeState();
}

class _SeancesHomeState extends State<SeancesHome> {
  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Scaffold(
        //Up part of the screen : the app bar
        appBar: AppBar(
            titleSpacing: 0.0,
            elevation: 0,
            backgroundColor: Theme.of(context).scaffoldBackgroundColor,
            leading: IconButton(
              icon: const Icon(Icons.menu_rounded),
              color: Colors.teal,
              //tooltip: 'Navigation menu',
              onPressed: () => {}, // TODO
            ),
            title: const Text('SAPA Mobile'),
            foregroundColor: Colors.teal,
            actions: [
              IconButton(
                icon: const Icon(Icons.power_settings_new_rounded),
                color: Colors.teal,
                onPressed: () => {}, // TODO
              ),
            ]),

        body: Center(
          child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(25.0, 0.0, 25.0, 20.0),
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    //Brique Programme PEPS
                    Row(mainAxisAlignment: MainAxisAlignment.start, children: [
                      // Brique d'affichage de la prochaine séance
                      const Row(
                        //  crossAxisAlignment: CrossAxisAlignment.center,
                        //1
                        children: [
                          Text('Prochaine séance prévue le : '),
                          Text('lundi 23 nov., 14h-15h')
                        ],
                      ),
                      //Bouton pour s'inscrire à une séance PEPS
                      Expanded(
                        flex: 1,
                        child: Align(
                          alignment: Alignment.center,
                          // padding: EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 0.0),
                          // Bouton texte redirigeant vers la page d'inscription à une séance
                          child: TextButton(
                            style: ButtonStyle(
                              foregroundColor:
                                  MaterialStateProperty.all<Color>(Colors.teal),
                            ),
                            onPressed: () {},
                            child: const Text(
                                "S'inscrire à une séance du programme"), //TODO : Lien vers séance pg PEPS
                          ),
                        ),
                      ),
                      Expanded(
                        flex: 2,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.center,
                          children: [
                            const Text(
                                'Historique de mes dernières séances'), //TODO: faire un texte cliquable pour afficher l'historique
                            TextButton(
                              style: ButtonStyle(
                                foregroundColor:
                                    MaterialStateProperty.all<Color>(
                                        Colors.teal),
                              ),
                              onPressed: () {},
                              //TODO : display a list of the last seances
                              child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.center,
                                  children: [
                                    Center(
                                      child: ElevatedButton(
                                        child: const Text(
                                          'Historique complet de mes séances',
                                          style: TextStyle(
                                            color: Colors.white,
                                          ),
                                        ),
                                        onPressed: () {
                                          showDialog(
                                            context: context,
                                            builder: (BuildContext context) =>
                                                _buildPopupDialog(context),
                                          );
                                        },
                                      ),
                                    ),
                                  ]),
                            ),
                          ],
                        ),
                      ),
                    ]),
                    //Partie Sport en autonomie
                    Row(
                        //1
                        children: [
                          Expanded(
                            flex: 1,
                            child: Align(
                              alignment: Alignment.center,
                              // padding: EdgeInsets.fromLTRB(0.0, 0.0, 0.0, 0.0),
                              // Bouton texte redirigeant vers la page d'inscription à une séance
                              child: TextButton(
                                style: ButtonStyle(
                                  foregroundColor:
                                      MaterialStateProperty.all<Color>(
                                          Colors.teal),
                                ),
                                onPressed: () {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                        builder: (context) => const maSeance(
                                              title: 'Créer une séance',
                                            )),
                                  );
                                },
                                child:
                                    const Text("Créer une séance en autonomie"),
                              ),
                            ),
                          ),
                          Expanded(
                            flex: 2,
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.center,
                              children: [
                                TextButton(
                                  style: ButtonStyle(
                                    foregroundColor:
                                        MaterialStateProperty.all<Color>(
                                            Colors.teal),
                                  ),
                                  onPressed: () {},
                                  //TODO : display a list of the last seances
                                  child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.center,
                                      children: [
                                        Center(
                                          child: ElevatedButton(
                                            child: const Text(
                                              'Voir mes séances en autonomie créées',
                                              style: TextStyle(
                                                color: Colors.white,
                                              ),
                                            ),
                                            onPressed: () {
                                              showDialog(
                                                context: context,
                                                builder: (BuildContext
                                                        context) =>
                                                    _buildPopupDialog(context),
                                              );
                                            },
                                          ),
                                        ),
                                      ]),
                                ),
                              ],
                            ),
                          ),
                        ]),
                    //Display du carousel_slider
                    Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            flex: 1,
                            child: Column(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceAround,
                                children: [
                                  const Text("En manque d'inspiration ?"),
                                  CarouselSlider(
                                    items: [
                                      //1st Youtube video link
                                      Container(
                                        margin: const EdgeInsets.all(6.0),
                                        decoration: BoxDecoration(
                                          borderRadius:
                                              BorderRadius.circular(8.0),
                                          image: const DecorationImage(
                                            image: AssetImage(
                                                "assets/fonts/carrouselSeance/screen1.jpg"),
                                            fit: BoxFit.cover,
                                          ),
                                        ),
                                      ),

                                      //2nd Youtube video link
                                      Container(
                                        margin: const EdgeInsets.all(6.0),
                                        decoration: BoxDecoration(
                                          borderRadius:
                                              BorderRadius.circular(8.0),
                                          image: const DecorationImage(
                                            image: AssetImage(
                                                "assets/fonts/carrouselSeance/screen2.jpg"),
                                            fit: BoxFit.cover,
                                          ),
                                        ),
                                      ),

                                      //3rd Youtube video link
                                      Container(
                                        margin: const EdgeInsets.all(6.0),
                                        decoration: BoxDecoration(
                                          borderRadius:
                                              BorderRadius.circular(8.0),
                                          image: const DecorationImage(
                                            image: AssetImage(
                                                "assets/fonts/carrouselSeance/screen3.jpg"),
                                            fit: BoxFit.cover,
                                          ),
                                        ),
                                      ),

                                      //4th Youtube video link
                                      Container(
                                        margin: const EdgeInsets.all(6.0),
                                        decoration: BoxDecoration(
                                          borderRadius:
                                              BorderRadius.circular(8.0),
                                          image: const DecorationImage(
                                            image: AssetImage(
                                                "assets/fonts/carrouselSeance/screen4.jpg"),
                                            fit: BoxFit.fill,
                                          ),
                                        ),
                                      ),

                                      //5th Youtube video link
                                      Container(
                                        margin: const EdgeInsets.all(6.0),
                                        decoration: BoxDecoration(
                                          borderRadius:
                                              BorderRadius.circular(8.0),
                                          image: const DecorationImage(
                                            image: AssetImage(
                                                "assets/fonts/carrouselSeance/screen5.jpg"),
                                            fit: BoxFit.cover,
                                          ),
                                        ),
                                      ),

                                      //6th Youtube video link
                                      Container(
                                        margin: const EdgeInsets.all(6.0),
                                        decoration: BoxDecoration(
                                          borderRadius:
                                              BorderRadius.circular(8.0),
                                          image: const DecorationImage(
                                            image: AssetImage(
                                                "assets/fonts/carrouselSeance/screen6.jpg"),
                                            fit: BoxFit.cover,
                                          ),
                                        ),
                                      ),
                                    ],
                                    options: CarouselOptions(
                                      height: 180.0,
                                      enlargeCenterPage: true,
                                      autoPlay: true,
                                      aspectRatio: 2.0,
                                      autoPlayCurve: Curves.fastOutSlowIn,
                                      enableInfiniteScroll: true,
                                      autoPlayAnimationDuration:
                                          const Duration(milliseconds: 800),
                                      viewportFraction: 0.8,
                                      pageViewKey: const PageStorageKey<String>(
                                          'carousel_slider'),
                                    ),
                                  ),
                                ]),
                          )
                        ]),
                    //Carousel des vidéos YouTube
                  ])),
        ),
      ),
    );
  }
}

Widget _buildPopupDialog(BuildContext context) {
  return AlertDialog(
    title: const Text('Historique complet de mes séances'),
    content: const Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: <Widget>[
        Text("Séance 1"),
        Text("Séance 2"),
        Text("Séance 3"),
        Text("Séance 4"),
        Text("Séance 5"),
        Text("Séance 6"),
      ],
    ),
    actions: <Widget>[
      TextButton(
        onPressed: () {
          Navigator.of(context).pop();
        },
        child: const Text('Close'),
      ),
    ],
  );
}

/// ******************************************************************//*
