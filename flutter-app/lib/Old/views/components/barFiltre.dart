import 'package:flutter/material.dart';

class filtreBar extends StatefulWidget {
  const filtreBar({Key? key}) : super(key: key);

  @override
  State<filtreBar> createState() => _filtreBarState();
}

class _filtreBarState extends State<filtreBar> {
  int _selectedIndex = 0;

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(15.0, 0.0, 15.0, 20.0),
      height: 50,
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          const Text(
            'XXX résutats de recherche',
          ),
          Row(
            children: [
              const Text(
                'Filtre',
              ),
              IconButton(
                icon: Icon(Icons.filter_list_outlined,
                    color: Theme.of(context).primaryColor),
                onPressed: null,
              ),
            ],
          )
        ],
      ),
    );
  }
}
