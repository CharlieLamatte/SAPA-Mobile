import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../Messagerie_Widgets/glowing_action_button.dart';

class ActionBar extends StatelessWidget {
  const ActionBar({
    Key? key
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      bottom: true,
      top: false,
      child: Row(
        children: [
          Container(
            decoration: BoxDecoration(
              border: Border(
                right: BorderSide(
                  width: 2,
                  color: Theme.of(context).primaryColor,
                ),
              ),
            ),
            child: const Padding(
              padding: EdgeInsets.symmetric(horizontal: 16.0),
              child: Icon(
                CupertinoIcons.camera_fill,
              ),
            ),
          ),
          const Expanded(
            child: Padding(
              padding: EdgeInsets.only(left: 16.0),
              child: TextField(
                style: TextStyle(
                  fontSize: 14,
                ),
                decoration: InputDecoration(
                  hintText: 'Type something...',
                  border: InputBorder.none,
                ),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.only(
              left: 12,
              right: 24.0,
            ),
            child: GlowingActionButton(
                color: Theme.of(context).primaryColor,
                icon: Icons.send_rounded,
                onPressed: () => {
                  showDialog(
                      context: context,
                      builder: (BuildContext _) {
                        return const AlertDialog(
                          title: Text('🔨 Information 🔨'),
                          content: Text('A développer - Doit permettre d\'envoyer un message !'),
                        );
                      }
                  ),
                }
            ),
          ),
        ],
      ),
    );
  }
}
