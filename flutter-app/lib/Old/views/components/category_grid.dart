import 'package:flutter/material.dart';
import 'package:flutter_application_3/models/category.dart';
import 'package:flutter_application_3/old/views/pages/module_MaProgression/progression.dart';

class CategoryGrid extends StatelessWidget {
  final Function() updateDarkThemeState;

  CategoryGrid(this.updateDarkThemeState, {Key? key}) : super(key: key);

  final _categories = [
    Category("Suivi PEPS", Colors.pink, "category2.jpg"),
    Category("Ma Progression", Colors.orange, "category4.jpg"),
    Category("Mon Programme", Colors.blue, "category1.jpg"),
    Category("Ma Messagerie", Colors.green, "category3.jpg"),
  ];

  final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

  List<Widget> getChildren(BuildContext context) {
    return _categories.map<Widget>((category) {
      return Card(
        child: Stack(
          children: [
            ColorFiltered(
                colorFilter:
                    const ColorFilter.mode(Colors.grey, BlendMode.modulate),
                child: Container(
                  decoration: BoxDecoration(
                    border: Border.all(color: category.color, width: 2.0),
                    borderRadius: const BorderRadius.all(Radius.circular(10.0)),
                    image: DecorationImage(
                      image: AssetImage("assets/${category.image}"),
                    ),
                  ),
                )),
            SizedBox(
              width: double.infinity,
              height: double.infinity,
              child: ElevatedButton(
                onPressed: ()
                    //{
                    //   if (category.name.toString() == 'Suivi PEPS') {
                    //   } else if (category.name.toString().toLowerCase() ==
                    //       'Ma Progression') {
                    //     // Navigator.push(
                    //     //   context,
                    //     //   MaterialPageRoute(
                    //     //     builder: (context) =>
                    //     //         ProgressPage(updateDarkThemeState),
                    //     //   ),
                    //     // );
                    //   } else if (category.name.toString().toLowerCase() ==
                    //       'Mon Programme') {
                    //   } else if (category.name.toString().toLowerCase() ==
                    //       'Ma Messagerie') {
                    //     Navigator.push(
                    //       context,
                    //       MaterialPageRoute(
                    //           builder: (context) =>
                    //               ProgressPage(updateDarkThemeState)),
                    //     );
                    //   }
                    // },

                    =>
                    {
                  if (category.name.toString() == 'Suivi PEPS')
                    {
                      // Navigator.push(
                      //   context,
                      //   MaterialPageRoute(
                      //     builder: (context) =>
                      //         ProgressPage(updateDarkThemeState),
                      //   ),
                      // ),
                    }
                  else if (category.name.toString() == 'Ma Progression')
                    {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) =>
                              ProgressPage(updateDarkThemeState),
                        ),
                      ),
                    }
                  else if (category.name.toString() == 'Mon Programme')
                    {
                      //   Navigator.push(
                      //     context,
                      //     MaterialPageRoute(
                      //       builder: (context) =>
                      //           ProgressPage(updateDarkThemeState),
                      //     ),
                      //   ),
                    }
                  else if (category.name.toString() == 'Ma Messagerie')
                    {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) =>
                              ProgressPage(updateDarkThemeState),
                        ),
                      ),
                    }
                },
                style: ElevatedButton.styleFrom(
                  elevation: 0.0, backgroundColor: Colors.red.withOpacity(0),
                  shape: const RoundedRectangleBorder(
                    borderRadius: BorderRadius.all(
                      Radius.circular(10),
                    ),
                    // side: BorderSide(color: Colors.white)
                  ),
                ),
                child: null,
              ),
            ),
            Container(
                padding: const EdgeInsets.all(10),
                child: Align(
                    alignment: Alignment.bottomRight,
                    child: Text(
                      category.name,
                      style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 18,
                          color: Colors.white),
                    )))
          ],
        ),
      );
    }).toList();
  }

  late var _ctx;
  @override
  Widget build(BuildContext context) {
    _ctx = context;

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
