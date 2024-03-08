import 'dart:collection';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_application_3/Utils/sessionManager.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:table_calendar/table_calendar.dart';
import 'package:syncfusion_flutter_calendar/calendar.dart';

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

  factory Evenement.fromJson(Map<String, dynamic> json) {
    return Evenement(
      title: json['nom_creneau'],
      description: json['type_parcours'],
      from: DateTime.parse(json['date_seance'] + 'T' + json['heure_debut']),
      to: DateTime.parse(json['date_seance'] + 'T' + json['heure_fin']),
    );
  }

  @override
  String toString() => title;

  String fromTimeToString() {
    return DateFormat('kk:mm').format(from);
  }

  String toTimeToString() {
    return DateFormat('kk:mm').format(to);
  }
}

final kEvents = LinkedHashMap<DateTime, List<Evenement>>(
  equals: isSameDay,
  hashCode: getHashCode,
);

Future<Map<DateTime, List<Evenement>>> fetchEventsFromAPI() async {
  var url = Uri.parse('http://127.0.0.1:8000/Seances/GetAll');
  Map<String, String> headers = {"Content-type": "application/json"};
  Map<String, dynamic> jsonBody = {
    "username": SessionManager.username,
    "password": SessionManager.password,
    "id_user": SessionManager.id_user
  };
  String requestBody = json.encode(jsonBody);

  var response = await http.post(
    url,
    headers: headers,
    body: requestBody,
  );

  // Analyser la réponse JSON
  final jsonData = json.decode(response.body);

  // Convertir les données JSON en un format de Map<DateTime, List<Evenement>>
  Map<DateTime, List<Evenement>> eventsMap = {};

  jsonData.forEach((dateString, eventsData) {
    DateTime date = DateTime.parse(dateString);
    List<Evenement> eventsList = (eventsData as List).map((eventJson) {
      return Evenement.fromJson(eventJson);
    }).toList();
    eventsMap[date] = eventsList;
  });
  print(eventsMap);
  return eventsMap;
}

void main() {
  // Appel de fetchEventsFromAPI
  fetchEventsFromAPI().then((events) {
    // Mise à jour de kEvents avec les événements récupérés
    kEvents.addAll(events);
  });
}

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

// Méthode pour Afficher les séances dans listeSeanceList
final List<Appointment> sampleData = [
  Appointment(
    subject: "Rugby",
    startTime: DateTime.now(),
    endTime: DateTime.now().add(const Duration(hours: 1, minutes: 30)),
    color: const Color(0xFFD6D6D6),
  ),
  Appointment(
    subject: "Rugby",
    startTime: DateTime.now().add(const Duration(hours: 1, minutes: 30)),
    endTime: DateTime.now().add(const Duration(hours: 3)),
    color: const Color(0xFFD6D6D6),
  ),
  Appointment(
    subject: "Gym",
    startTime: DateTime.now().add(const Duration(days: 2)),
    endTime: DateTime.now().add(const Duration(days: 2, hours: 2)),
    color: const Color(0xFFD6D6D6),
  ),
];
