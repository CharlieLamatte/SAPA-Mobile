import 'dart:collection';

import 'package:flutter/material.dart';
import 'package:table_calendar/table_calendar.dart';

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
}

final kEvents = LinkedHashMap<DateTime, List<Evenement>>(
  equals: isSameDay,
  hashCode: getHashCode,
)..addAll(_kEventSource);

final _kEventSource = {
  for (var item in List.generate(50, (index) => index))
    DateTime.utc(kFirstDay.year, kFirstDay.month, item * 5): List.generate(
      item % 4 + 1,
          (index) => Evenement(
        title: 'Karaté - 14h/15h30',
        description: 'description',
        from: DateTime.now(),
        to: DateTime.now(),
      ),
    ),
}..addAll({
  kToday: [
    Evenement(
        title: 'Gym Kiné Equilibre Sénior - 10h/11h30',
        description: '',
        from: DateTime.now(),
        to: DateTime.now()),
    Evenement(
        title: 'Rugby - 15h/16h',
        description: '',
        from: DateTime.now(),
        to: DateTime.now()),
  ],
});

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
