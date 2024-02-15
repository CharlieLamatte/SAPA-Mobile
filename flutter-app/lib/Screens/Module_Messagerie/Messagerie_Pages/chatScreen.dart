import 'package:flutter_application_3/models/models.dart';
import '../Messagerie_Widgets/widgets.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../Messagerie_Components/componentsChat_screen.dart';

class ChatScreen extends StatelessWidget {
  static Route route(MessageData data) => MaterialPageRoute(
    builder: (context) => ChatScreen(
      messageData: data,
    ),
  );

  const ChatScreen({
    Key? key,
    required this.messageData,
  }) : super(key: key);

  final MessageData messageData;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        iconTheme: Theme.of(context).iconTheme,
        centerTitle: false,
        backgroundColor: Theme.of(context).primaryColor,
        elevation: 0,
        leadingWidth: 54,
        leading: Align(
          alignment: Alignment.centerRight,
          child: IconBackground(
            icon: CupertinoIcons.back,
            onTap: () {
              Navigator.of(context).pop();
            },
          ),
        ),
        title: AppBarTitle(
          messageData: messageData,
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: 0,
            ),
            child: Center(
              child: IconButton(
                  icon: const Icon(Icons.video_call),
                  color : Theme.of(context).splashColor,
                  onPressed: () => {
                    showDialog(
                        context: context,
                        builder: (BuildContext _) {
                          return const AlertDialog(
                            title: Text('🔨 Information 🔨'),
                            content: Text('A développer (ou non en fonction de l\'utilité) !'),
                          );
                        }
                    ),
                  }
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.only(right: 0),
            child: Center(
              child: IconButton(
                  icon: const Icon(Icons.phone),
                  color : Theme.of(context).splashColor,
                  onPressed: () => {
                    showDialog(
                        context: context,
                        builder: (BuildContext _) {
                          return const AlertDialog(
                            title: Text('🔨 Information 🔨'),
                            content: Text('A développer (ou non en fonction de l\'utilité) !'),
                          );
                        }
                    ),
                  }
              ),
            ),
          ),
        ],
      ),
      body: const Column(
        children: [
          Expanded(
            child: DemoMessageList(),
          ),
          ActionBar(),
        ],
      ),
    );
  }
}





