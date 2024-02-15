// import 'package:flutter/material.dart';
// import 'package:flutter_application_3/Module_Suivi_PEPS/Calendar/Model/event.dart';
// import 'package:flutter_application_3/Module_Suivi_PEPS/Calendar/Provider/event_provider.dart';
// import 'package:provider/provider.dart';
// import 'package:flutter_application_3/Module_Suivi_PEPS/Calendar/Utils.dart';
//*
// class EventEditingPage extends StatefulWidget {
//   final Event? event;
//
//   const EventEditingPage({
//     Key? key,
//     this.event,
//   }) : super(key: key);
//
//   @override
//   _EventEditingPageState createState() => _EventEditingPageState();
// }
//
// class _EventEditingPageState extends State<EventEditingPage> {
//   //Valide que au moins le titre est saisie
//   final _formKey = GlobalKey<FormState>();
//   final titleController = TextEditingController();
//   //Collecte des dates pour le calendrier
//   late DateTime fromDate; //TODO: à supp
//   late DateTime toDate; // TODO: à supp
//
//   @override
//   void initState() {
//     super.initState();
//
//     if (widget.event == null) {
//       fromDate = DateTime.now();
//       toDate = DateTime.now().add(Duration(hours: 2));
//     } else {
//       final event = widget.event!;
//
//       titleController.text = event.title;
//       fromDate = event.from;
//       toDate = event.to;
//     }
//   }
//
//   @override
//   void dispose() {
//     titleController.dispose();
//
//     super.dispose();
//   }
//
//   @override
//   Widget build(BuildContext context) => Scaffold(
//         appBar: AppBar(
//           leading: CloseButton(),
//           actions: buildEditingActions(),
//         ),
//         body: SingleChildScrollView(
//           padding: EdgeInsets.all(12),
//           child: Form(
//               key: _formKey,
//               child: Column(
//                 mainAxisAlignment: MainAxisAlignment.end,
//                 children: <Widget>[
//                   buildTitle(),
//                   SizedBox(height: 12),
//                   buildDateTimePicker(),
//                 ],
//               )),
//         ),
//       );
//
//   List<Widget> buildEditingActions() => [
//         ElevatedButton.icon(
//           style: ElevatedButton.styleFrom(
//             primary: Colors.transparent,
//             shadowColor: Colors.transparent,
//           ),
//           onPressed: saveForm,
//           icon: Icon(Icons.done),
//           label: Text('Sauvegarder'),
//         )
//       ];
//
//   Widget buildTitle() => TextFormField(
//         style: TextStyle(fontSize: 24),
//         decoration: InputDecoration(
//           border: UnderlineInputBorder(),
//           hintText: 'Ajouter un titre',
//         ),
//         onFieldSubmitted: (_) => saveForm(),
//         validator: (title) => title != null && title.isEmpty
//             ? 'Le titre ne peut pas être vide'
//             : null,
//         controller: titleController,
//       );
//
//   Widget buildDateTimePicker() => Column(
//         children: [
//           buildForm(),
//           buildTo(),
//         ],
//       );
//
//   Widget buildForm() => buildHeader(
//         header: 'FROM',
//         child: Row(
//           children: [
//             Expanded(
//               flex: 2,
//               child: buildDropdownField(
//                 text: Utils.toDate(fromDate),
//                 onClicked: () => pickFromDateTime(pickDate: true),
//               ),
//             ),
//             Expanded(
//               child: buildDropdownField(
//                 text: Utils.toTime(fromDate),
//                 onClicked: () => pickFromDateTime(pickDate: false),
//               ),
//             ),
//           ],
//         ),
//       );
//
//   Widget buildTo() => buildHeader(
//         header: 'TO',
//         child: Row(
//           children: [
//             Expanded(
//               flex: 2,
//               child: buildDropdownField(
//                 text: Utils.toDate(toDate),
//                 onClicked: () {},
//               ),
//             ),
//             Expanded(
//               child: buildDropdownField(
//                 text: Utils.toTime(toDate),
//                 onClicked: () {},
//               ),
//             ),
//           ],
//         ),
//       );
//
//   Future pickFromDateTime({required bool pickDate}) async {
//     final date = await pickDateTime(fromDate, pickDate: pickDate);
//     if (date == null) return;
//
//     ///**************************************************************************************
//     //Ne pas sélectionner une date antérieure à la prochaine date
//     if (date.isAfter(toDate)) {
//       toDate =
//           DateTime(date.year, date.month, date.day, toDate.hour, toDate.minute);
//     }
//
//     setState(() => fromDate = date);
//   }
//
//   Future pickToDateTime({required bool pickDate}) async {
//     final date = await pickDateTime(toDate, pickDate: pickDate);
//     if (date == null) return;
//
//     //Ne pas sélectionner une date antérieure à la prochaine date
//     if (date.isAfter(toDate)) {
//       toDate =
//           DateTime(date.year, date.month, date.day, toDate.hour, toDate.minute);
//     }
//
//     setState(() => toDate = date);
//   }
//
//   ///**************************************************************************************
//
//   Future<DateTime?> pickDateTime(
//     DateTime initialDate, {
//     required bool pickDate,
//     DateTime? firstDate,
//   }) async {
//     if (pickDate) {
//       //Display th date picker
//       final date = await showDatePicker(
//         context: context,
//         initialDate: initialDate,
//         firstDate: firstDate ?? DateTime(2015, 8),
//         lastDate: DateTime(2101),
//       );
//       if (date == null) return null;
//
//       final time =
//           Duration(hours: initialDate.hour, minutes: initialDate.minute);
//
//       return date.add(time);
//     } else {
//       final timeOfDay = await showTimePicker(
//         context: context,
//         initialTime: TimeOfDay.fromDateTime(initialDate),
//       );
//       if (timeOfDay == null) return null;
//
//       final date =
//           DateTime(initialDate.year, initialDate.month, initialDate.day);
//       final time =
//           Duration(hours: initialDate.hour, minutes: initialDate.minute);
//
//       return date.add(time);
//     }
//   }
//
//   Widget buildDropdownField({
//     required String text,
//     required VoidCallback onClicked,
//   }) =>
//       ListTile(
//         title: Text(text),
//         trailing: Icon(Icons.arrow_drop_down),
//         onTap: onClicked,
//       );
//
//   buildHeader({
//     required String header,
//     required Widget child,
//   }) =>
//       Column(
//         crossAxisAlignment: CrossAxisAlignment.start,
//         children: [
//           Text(
//             header,
//             style: TextStyle(fontWeight: FontWeight.bold),
//           )
//         ],
//       );
//
//   //Sauver l'évènement créé
//   Future saveForm() async {
//     final isValid = _formKey.currentState!.validate();
//
//     if (isValid) {
//       final event = Event(
//         title: titleController.text,
//         description: 'Description',
//         from: fromDate,
//         to: toDate,
//         isAllDay: false,
//       );
//
//       ///Allows to modify an event
//       final isEditing = widget.event != null;
//
//       final provider = Provider.of<EventProvider>(context, listen: true);
//       provider.addEvent(event);
//
//       if (isEditing) {
//         provider.editEvent(event, widget.event!);
//
//         Navigator.of(context).pop();
//       } else {
//         provider.addEvent(event);
//       }
//
//       Navigator.of(context).pop();
//     }
//   }
// }
