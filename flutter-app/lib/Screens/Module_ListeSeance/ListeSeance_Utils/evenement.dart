import 'dart:collection';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_application_3/Utils/sessionManager.dart';
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

  factory Evenement.fromJson(Map<String, dynamic> json) {
    return Evenement(
      title: json['type_seance'] ?? '',
      description: json['nom_creneau'] ?? '',
      from: DateTime.parse(json['heure_debut'] ?? ''),
      to: DateTime.parse(json['heure_fin'] ?? ''),
    );
  }

Future<void> fetchSeances(int userId) async {
  var url = Uri.parse('http://127.0.0.1:8000/login');
    Map<String, String> headers = {"Content-type": "application/json"};
    Map<String, dynamic> jsonBody = {"username": SessionManager.username, "password": SessionManager.password, "id_user": SessionManager.id_user};
    String requestBody = json.encode(jsonBody);

    try {
      var response = await http.post(
        url,
        headers: headers,
        body: requestBody,
      );

      final jsonResponse = json.decode(response.body);

      if (response.statusCode == 200) {
        addEvents = {};
        for (dynamic seanceData in jsonResponse) {
          DateTime date = DateTime.parse(formattedString)
        }
    }

  if (response.statusCode == 200) {
    // Si la requête réussit, décoder le JSON et créer une liste d'objets Seance
    List<dynamic> jsonResponse = json.decode(response.body);

        // Convert the decoded data to the required format
        addEvents = {};
        for (dynamic eventData in jsonResponse) {
          print('Event Data: $eventData');
          DateTime date = DateTime.parse(eventData['date_seance']);
          print('Date: $date');
          if (!addEvents.containsKey(date)) {
            addEvents[date] = [];
          }
          addEvents[date]!.add(
            Evenement(
              title: eventData['nom_creneau'],
              description: eventData['type_seance'],
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
