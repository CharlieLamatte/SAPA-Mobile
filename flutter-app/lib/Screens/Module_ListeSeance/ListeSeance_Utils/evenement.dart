import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter_application_3/Utils/sessionManager.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
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

// ignore: body_might_complete_normally_nullable
Future<Map<DateTime, List<Evenement>>?> fetchEventsFromAPI(
    BuildContext context) async {
  var url = Uri.parse('http://127.0.0.1:8000/Seances/GetAll');
  Map<String, String> headers = {"Content-type": "application/json"};
  Map<String, dynamic> jsonBody = {
    "username": SessionManager.username,
    "password": SessionManager.password,
    "id_user": SessionManager.id_user
  };
  String requestBody = json.encode(jsonBody);

  try {
    var response = await http.post(
      url,
      headers: headers,
      body: requestBody,
    );

    // Analyze the JSON response
    final List<dynamic> jsonDataList = json.decode(response.body);

    if (response.statusCode == 200) {
      // Convert JSON data into a Map<DateTime, List<Evenement>>
      Map<DateTime, List<Evenement>> eventsMap = {};
      sampleData.clear();

      for (var jsonData in jsonDataList) {
        // Create an Evenement object from each JSON object
        Evenement evenement = Evenement.fromJson(jsonData);

        // Extract the date from the Evenement object
        DateTime date = evenement.from;

        // Check if the date exists in the map, if not, create a new list
        if (!eventsMap.containsKey(date)) {
          eventsMap[date] = [];
        }

        // Add the Evenement object to the list corresponding to the date
        eventsMap[date]!.add(evenement);
        sampleData.add(Appointment(
          subject: evenement.title,
          startTime: evenement.from,
          endTime: evenement.to,
          color: const Color(0xFFD6D6D6),
        ));
      }

      return eventsMap;
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content:
              Text('Error communicating with the database: ${response.body}'),
          duration: const Duration(seconds: 3),
        ),
      );
      return null;
    }
  } catch (e) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Error connecting. $e'),
        duration: const Duration(seconds: 3),
      ),
    );
    return null;
  }
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

int getHashCode(DateTime key) {
  return key.day * 1000000 + key.month * 10000 + key.year;
}

class MeetingDataSource extends CalendarDataSource {
  MeetingDataSource(List<Appointment> source) {
    appointments = source;
  }
}

// Méthode pour Afficher les séances dans listeSeanceList
final List<Appointment> sampleData = [];
