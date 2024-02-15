// import 'package:flutter/material.dart';
// //import 'package:flutter_application_3/Module_Suivi_PEPS/Calendar/Page/event_editing_page.dart';
// //import 'package:flutter_application_3/Module_Suivi_PEPS/Calendar/Provider/event_provider.dart';
// //import 'package:flutter_application_3/Module_Suivi_PEPS/Calendar/Model/event.dart';
// import 'package:flutter_application_3/Module_Suivi_PEPS/Calendar/suiviPepsCalendrier.dart';
// import 'package:provider/provider.dart';
// import 'package:flutter_application_3/Module_Suivi_PEPS/Calendar/Utils.dart';
//*
// class EventViewingPage extends StatelessWidget {
//   final Event event;
//
//   const EventViewingPage({
//     Key? key,
//     required this.event,
//   }) : super(key: key);
//
//   @override
//   Widget build(BuildContext context) => Scaffold(
//         appBar: AppBar(
//           leading: const CloseButton(),
//           //actions: buildViewingActions(context, event),
//         ),
//         body: ListView(
//           padding: const EdgeInsets.all(32),
//           children: <Widget>[
//             //buildDateTime(event),
//             const SizedBox(height: 32),
//             Text(
//               event.title,
//               //TODO : style: TextStyle(fontSize: 24, fontWeight: FontWeight),
//             ),
//             const SizedBox(height: 24),
//             Text(
//               event.description,
//               style: const TextStyle(color: Colors.white, fontSize: 18),
//             ),
//           ],
//         ),
//       );
//
//    Widget buildDateTime(Event event) {
//     return Column(
//       children: [
//         buildDate(
//             event.isAllDay ? 'Toute la journée' : 'De', event.from), //TODO
//         if (!event.isAllDay) buildDate('A', event.to),
//       ],
//     );
//   }
//
//   Widget buildDate(String title, DateTime date) {
//     //TODO
//   }
//
//   List<Widget> buildViewingActions(BuildContext context, Event event) {
//     IconButton(
//       icon: const Icon(Icons.edit),
//       onPressed: () => Navigator.of(context).pushReplacement(
//         MaterialPageRoute(
//           builder: (context) => EventEditingPage(event: event),
//         ),
//       ),
//     );
//     IconButton(
//       icon: const Icon(Icons.delete),
//       onPressed: () {
//         final provider = Provider.of<EventProvider>(context, listen: true);
//
//         //provider.deleteEvent(event); //TODO
//       },
//     );
//   }
// }
