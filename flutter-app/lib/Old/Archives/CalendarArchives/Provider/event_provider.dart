/*
import 'package:flutter_application_3/Module_Suivi_PEPS/Calendar/Model/event.dart';
import 'package:flutter_application_3/Module_Suivi_PEPS/Calendar/Utils.dart';
import 'package:flutter/cupertino.dart';
*
class EventProvider extends ChangeNotifier {
  final List<Event> _events = [];

  List<Event> get events => _events;

  ///Save the date that is selected into the calendar
  DateTime _selectedDate = DateTime.now();
  DateTime get selectedDate => _selectedDate;
  void setDate(DateTime date) => _selectedDate = date;
  List<Event> get eventsOfSelectedDate => _events;

  void addEvent(Event event) {
    _events.add(event);

    notifyListeners();
  }

  void editEvent(Event newEvent, Event oldEvent) {
    final index = _events.indexOf(oldEvent);
    _events[index] = newEvent;

    notifyListeners();
  }
}
*/
