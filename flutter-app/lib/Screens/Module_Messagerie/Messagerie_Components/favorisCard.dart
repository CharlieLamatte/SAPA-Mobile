import 'package:flutter/material.dart';
import 'package:flutter_application_3/models/favorisData.dart';
import '../Messagerie_Widgets/widgets.dart';

class FavorisCard extends StatelessWidget {
  const FavorisCard({
    Key? key,
    required this.favorisData,
  }) : super(key: key);

  final FavorisData favorisData;

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: MainAxisSize.min,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Avatar.medium(
            url: favorisData.url
        ),
        Expanded(
          child: Padding(
            padding: const EdgeInsets.only(
                top: 5.0
            ),
            child: Text(
              favorisData.name,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                fontSize: 11,
                letterSpacing: 0.3,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ),
      ],
    );
  }
}
