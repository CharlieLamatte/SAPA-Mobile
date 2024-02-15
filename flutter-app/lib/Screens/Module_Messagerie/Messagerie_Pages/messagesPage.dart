import 'package:faker/faker.dart';
import 'package:flutter/material.dart';
import 'package:flutter_application_3/models/messageData.dart';
import 'package:jiffy/jiffy.dart';
import '../helpers.dart';
import '../Messagerie_Components/componentsMessages_page.dart';

class MessagesPage extends StatelessWidget {
  const MessagesPage({
    Key? key
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch, // étire sur tt l'écran
        children: [
          const Favoris(),
          Expanded(
            child: CustomScrollView(
              slivers: [
                SliverList(
                  delegate: SliverChildBuilderDelegate(_delegate),
                )
              ],
            ),
          )
        ],
      ),
    );
  }

  Widget _delegate(BuildContext context, int index) {
    final Faker faker = Faker();
    final date = Helpers.randomDate();
    return MessageTitle(
      messageData: MessageData(
        senderName: faker.person.name(),
        message: faker.lorem.sentence(),
        messageDate: date,
        dateMessage: Jiffy(date).fromNow(),
        profilePicture: Helpers.randomPictureUrl(),
      ),
    );
  }
}



