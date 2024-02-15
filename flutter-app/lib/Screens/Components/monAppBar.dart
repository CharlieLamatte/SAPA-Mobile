import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Module_Accueil/myHome.dart';

class MonAppBar extends AppBar {
  final String? myTitle;

  MonAppBar({
    Key? key,
    this.myTitle,
  }) : super(key: key);

  @override
  MonAppBarState createState() => MonAppBarState();
}

class MonAppBarState extends State<MonAppBar>{

  @override
  PreferredSizeWidget build(BuildContext context) {
    return
      AppBar(
        titleSpacing: 0.0,
        title: Text(widget.myTitle.toString()),
        backgroundColor: Theme.of(context).primaryColor,
        actions: [
          IconButton(
            icon: const Icon(Icons.home_rounded),
            color: Theme.of(context).splashColor,
            onPressed: () =>
                Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const MyHomePage(),
                    )
                ),
          ),
        ],
      );
  }
}