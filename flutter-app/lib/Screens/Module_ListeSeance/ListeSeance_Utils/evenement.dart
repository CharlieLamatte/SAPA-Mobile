import 'dart:collection';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:table_calendar/table_calendar.dart';
import 'package:syncfusion_flutter_calendar/calendar.dart';
import 'package:http/http.dart' as http;

class Evenement {
  final String title;
  final String description;
  final DateTime from;
  final DateTime to;
  final Color backgroundColor;
  final bool isAllDay;

  const Evenement({
    required this.title,
    required this.description,
    required this.from,
    required this.to,
    this.backgroundColor = Colors.teal,
    this.isAllDay = false,
  });

  @override
  String toString() => title;

  String fromTimeToString() {
    return DateFormat('kk:mm').format(from);
  }

  String toTimeToString() {
    return DateFormat('kk:mm').format(to);
  }

  static Future<void> fetchEvents() async {
    print('test0');
    try {
      final response = await http.post(
        Uri.parse(
            'suivibeneficiairestest.sportsante86.fr/PHP/Seances/ReadAllSeance.php'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'id_user': '92'}), // Replace with actual user ID
      );
      print('test1');

      if (response.statusCode == 200) {
        List<dynamic> decodedData = json.decode(response.body);

        // Convert the decoded data to the required format
        addEvents = {};
        for (dynamic eventData in decodedData) {
          print('test2');
          print('Event Data: $eventData');
          DateTime date = DateTime.parse(eventData['heure_debut']);
          print('Date: $date');
          if (!addEvents.containsKey(date)) {
            addEvents[date] = [];
          }
          addEvents[date]!.add(
            Evenement(
              title: eventData['type_seance'],
              description: eventData['nom_creneau'],
              from: DateTime.parse(eventData['heure_debut']),
              to: DateTime.parse(eventData['heure_fin']),
            ),
          );
        }

        kEvents.clear();
        kEvents.addAll(addEvents);

        // Update the 'sampleData' variable if you need it for something else
        sampleData = addEvents.entries
            .expand((entry) => entry.value)
            .map((event) => Appointment(
                  subject: event.title,
                  startTime: event.from,
                  endTime: event.to,
                  color: const Color(0xFFD6D6D6),
                ))
            .toList();
      } else {
        // Handle error when fetching data from the server
        print('Error else fetching events: ${response.statusCode}');
      }
    } catch (e) {
      // Handle other errors
      print('Error catch fetching events: $e');
    }
  }
}

final kEvents = LinkedHashMap<DateTime, List<Evenement>>(
  equals: isSameDay,
  hashCode: getHashCode,
)..addAll(addEvents);

Map<DateTime, List<Evenement>> addEvents = {};

//final addEvents = {
//  kToday: [
//    Evenement(
//        title: "Rugby",
//        description: "La description",
//        from: DateTime.now(),
//        to: DateTime.now().add(const Duration(hours: 1, minutes: 30))),
//    Evenement(
//        title: "Rugby",
//        description: "La description",
//        from: DateTime.now().add(const Duration(hours: 1, minutes: 30)),
//        to: DateTime.now().add(const Duration(hours: 3)))
//  ],
// DateTime.now().add(const Duration(days: 2)): [
//    Evenement(
//        title: "Gym",
//        description: "Ma description",
//        from: DateTime.now().add(const Duration(days: 2)),
//        to: DateTime.now().add(const Duration(days: 2, hours: 2))),
//  ]
//};

int getHashCode(DateTime key) {
  return key.day * 1000000 + key.month * 10000 + key.year;
}

/// Returns a list of [DateTime] objects from [first] to [last], inclusive.
List<DateTime> daysInRange(DateTime first, DateTime last) {
  final dayCount = last.difference(first).inDays + 1;
  return List.generate(
    dayCount,
    (index) => DateTime.utc(first.year, first.month, first.day + index),
  );
}

final kToday = DateTime.now();
final kFirstDay = DateTime(kToday.year, kToday.month - 3, kToday.day);
final kLastDay = DateTime(kToday.year, kToday.month + 3, kToday.day);

class MeetingDataSource extends CalendarDataSource {
  MeetingDataSource(List<Appointment> source) {
    appointments = source;
  }
}

List<Appointment> sampleData = [];

//final List<Appointment> sampleData = [
//  Appointment(
//    subject: "Rugby",
//    startTime: DateTime.now(),
//    endTime: DateTime.now().add(const Duration(hours: 1, minutes: 30)),
//    color: const Color(0xFFD6D6D6),
//  ),
//  Appointment(
//    subject: "Rugby",
//    startTime: DateTime.now().add(const Duration(hours: 1, minutes: 30)),
//    endTime: DateTime.now().add(const Duration(hours: 3)),
//    color: const Color(0xFFD6D6D6),
//  ),
//  Appointment(
//    subject: "Gym",
//    startTime: DateTime.now().add(const Duration(days: 2)),
//    endTime: DateTime.now().add(const Duration(days: 2, hours: 2)),
//    color: const Color(0xFFD6D6D6),
//  ),
//];
