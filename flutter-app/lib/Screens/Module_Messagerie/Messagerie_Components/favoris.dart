import 'package:faker/faker.dart';
import 'package:flutter/material.dart';
import 'package:flutter_application_3/models/favorisData.dart';
import '../helpers.dart';
import '../Messagerie_Components/componentsMessages_page.dart';

class Favoris extends StatelessWidget {
  const Favoris({
    Key? key
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 0,
      child: SizedBox(
        height: 140,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
                children: [
                  Expanded(
                    child:
                    Container(
                      padding: const EdgeInsets.fromLTRB(10.0, 0.0, 0.0, 0.0),
                      child : Text(
                        'Favoris',
                        style: TextStyle(
                          fontWeight: FontWeight.w900,
                          fontSize: 15,
                          color: Theme.of(context).focusColor,
                        ),
                      ),
                    ),
                  ),
                  Expanded(
                    child: IconButton(
                      alignment: Alignment.centerRight,
                      icon: const Icon(Icons.search),
                      color: Theme.of(context).focusColor,
                      onPressed: () => {
                        showDialog(
                            context: context,
                            builder: (BuildContext _) {
                              return const AlertDialog(
                                title: Text('🔨 Information'),
                                content: Text('A développer (recherche d\'une conversation ou d\'un utilisateur) !'),
                              );
                            }
                        ),
                      },
                    ),
                  )
                ]
            ),
            Expanded(
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemBuilder: (BuildContext context, int index) {
                  final faker = Faker();
                  return Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: SizedBox(
                      width: 60,
                      child: FavorisCard(
                        favorisData: FavorisData(
                          name: faker.person.name(),
                          url: Helpers.randomPictureUrl(),
                        ),
                      ),
                    ),
                  );
                },
              ),
            )
          ],
        ),
      ),
    );
  }
}
