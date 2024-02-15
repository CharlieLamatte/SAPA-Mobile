import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Module_Messagerie/Messagerie_Pages/homeChat.dart';
import 'package:flutter_application_3/models/category.dart';
import 'package:flutter_application_3/Screens/Module_ListeSeance/listeSeanceCal.dart';

class MyCategoryGrid extends StatefulWidget {
  const MyCategoryGrid({
    Key? key
  }) : super(key: key);

  @override
  _MyCategoryGridState createState() => _MyCategoryGridState();
}

class _MyCategoryGridState extends State<MyCategoryGrid> {

  final _categories = [ // TODO couleurs du thème
    Category("Mon Calendrier", Colors.pink, "category2.jpg"),
    Category("Ma Messagerie", Colors.orange, "category3.jpg"),
  ];

  final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

  List<Widget> getChildren(BuildContext context) {
    return _categories.map<Widget>((category) {
      return
          ElevatedButton(
            style: ButtonStyle(
              padding: MaterialStateProperty.all<EdgeInsets>(
                const EdgeInsets.all(0.0)
              ),
              shape: MaterialStateProperty.all<RoundedRectangleBorder>(
                RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10.0)
                )
              )
            ),
            child: Stack(
              children:[
                Container(
                  decoration: BoxDecoration(
                    image: DecorationImage(
                      image: AssetImage("assets/${category.image}"),
                      opacity: 0.6,
                    ),
                    border: Border.all(
                        color: category.color,
                        width: 2.0
                    ),
                    borderRadius: const BorderRadius.all(
                        Radius.circular(10.0)
                    ),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.all(10),
                  child: Align(
                    alignment: Alignment.bottomLeft,
                    child: Text(
                      category.name,
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 18,
                        color: Theme.of(context).splashColor,
                        shadows: const [
                          Shadow(
                            offset: Offset(0.0, 0.0), // position x et y
                            blurRadius: 15.0, // fondu
                            color: Color.fromARGB(220, 0, 0, 0), // couleur (avec a = opacité, r = rouge, g = vert, b = bleu)
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
            onPressed: () =>
            {

              // CALENDRIER ////////////////////////////////////////////////
              if (category.name.toString() == 'Mon Calendrier') {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => const ListeSeanceCal(),
                  ),
                ),
              }

              // Ma Messagerie ////////////////////////////////////////////
              else
                if (category.name.toString() == 'Ma Progression'){
                  // TODO myHomePage => ma progression
                  //  Navigator.push(
                  //    context,
                  //    MaterialPageRoute(
                  //      builder: (context) => const MaProgression(),
                  //    ),
                  //  ),
                  // START : pop-up de construction //
                  showDialog(
                      context: context,
                      builder: (BuildContext _) {
                        return const AlertDialog(
                          title: Text('🔨 Information 🔨', textAlign: TextAlign.center),
                          content: Text(
                              'Faire un lien vers la page de Ma progression (à construire)'
                          ),
                        );
                      }
                  ),
                  // END : pop-up de construction //

                }

                  // Ma Messagerie /////////////////////////////////////////////
                  else
                    if (category.name.toString() == 'Ma Messagerie') {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => HomeChat(),
                        ),
                      ),
                    }
            },
          );
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return GridView.count(
      primary: false,
      crossAxisCount: 2,
      mainAxisSpacing: 10,
      crossAxisSpacing: 10,
      shrinkWrap: true,
      padding: const EdgeInsets.all(10),
      children: getChildren(context),
    );
  }
}
