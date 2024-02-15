import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'componentsChat_screen.dart';


class DemoMessageList extends StatelessWidget {
  const DemoMessageList({
    Key? key
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8.0),
      child: ListView(
        children: const [
          DateLable(
              lable: 'Yesterday'
          ),
          MessageTile(
            message: 'Hi, Lucy! How\'s your day going?',
            messageDate: '12:01 PM',
          ),
          MessageOwnTile(
            message: 'You know how it goes...',
            messageDate: '12:02 PM',
          ),
          MessageTile(
            message: 'Do you want Starbucks?',
            messageDate: '12:02 PM',
          ),
          MessageOwnTile(
            message: 'Would be awesome!',
            messageDate: '12:03 PM',
          ),
          MessageTile(
            message: 'Coming up!',
            messageDate: '12:03 PM',
          ),
          MessageOwnTile(
            message: 'YAY!!!',
            messageDate: '12:03 PM',
          ),
        ],
      ),
    );
  }
}
