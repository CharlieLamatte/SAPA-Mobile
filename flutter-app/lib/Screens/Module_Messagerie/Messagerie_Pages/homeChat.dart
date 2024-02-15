import 'package:flutter_application_3/Screens/Components/monAppBar.dart';
import 'package:flutter_application_3/Screens/Components/maNavDrawer.dart';
import 'messagesPage.dart';
import 'package:flutter/material.dart';

class HomeChat extends StatelessWidget {
  HomeChat({
    Key? key
  }) : super(key: key);

  final ValueNotifier<int> pageIndex = ValueNotifier(0);
  final ValueNotifier<String> title = ValueNotifier('Messages');

  final pages = const [
    MessagesPage(),
  ];

  final pageTitles = const [
    'Messages',
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: MonAppBar(
          myTitle: 'Messagerie'
      ),
      drawer: const MyNavDrawer(),

      body: ValueListenableBuilder(
        valueListenable: pageIndex,
        builder: (BuildContext context, int value, _) {
          return pages[value];
        },
      ),
      floatingActionButton: FloatingActionButton(
          elevation: 10.0,
          child: const Icon(Icons.add),
          onPressed: () => {
            showDialog(
                context: context,
                builder: (BuildContext _) {
                  return const AlertDialog(
                    title: Text('🔨 Information'),
                    content: Text('A développer (créer une conversation avec liste de contacts) !'),
                  );
                }
            ),
          }
      ),
    );
  }
}






