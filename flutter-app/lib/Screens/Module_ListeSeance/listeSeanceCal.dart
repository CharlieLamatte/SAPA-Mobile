import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Components/maNavDrawer.dart';
import 'package:flutter_application_3/Screens/Components/monAppBar.dart';
import 'package:flutter_application_3/Screens/Module_ListeSeance/ListeSeance_Utils/evenement.dart';
import 'package:flutter_application_3/Screens/Module_ListeSeance/listeSeanceList.dart';
import 'package:table_calendar/table_calendar.dart';

import '../Module_DetailsSeance/maSeanceDetail.dart';

class ListeSeanceCal extends StatefulWidget {
  const ListeSeanceCal({Key? key}) : super(key: key);

  @override
  _ListeSeanceCalState createState() => _ListeSeanceCalState();
}

class _ListeSeanceCalState extends State<ListeSeanceCal> {
  late final ValueNotifier<List<Evenement>> _selectedEvents;
  CalendarFormat _calendarFormat = CalendarFormat.month;
  RangeSelectionMode _rangeSelectionMode = RangeSelectionMode
      .toggledOff; // Can be toggled on/off by longpressing a date
  DateTime _focusedDay = DateTime.now();
  DateTime? _selectedDay;
  DateTime? _rangeStart;
  DateTime? _rangeEnd;

  @override
  void initState() {
    super.initState();
    _selectedDay = _focusedDay;
    _populateEvents(); // Call method to populate kEvents
    _selectedEvents =
        ValueNotifier(_getEventsForDay(_selectedDay!) as List<Evenement>);
  }

  @override
  void dispose() {
    _selectedEvents.dispose();
    super.dispose();
  }

  Future<List<Evenement>> _getEventsForDay(DateTime day) async {
    await Evenement.fetchEvents(); // Call fetchEvents to update the events
    return kEvents[day] ?? [];
  }

  Future<void> _populateEvents() async {
    await Evenement.fetchEvents(); // Fetch events data
    setState(() {}); // Trigger a rebuild to reflect the updated kEvents
  }

  List<Evenement> eventsForDay(DateTime day) {
    // Check if kEvents contains events for the given day
    if (kEvents.containsKey(day)) {
      // Return the events for the given day
      return kEvents[day]!;
    } else {
      // Return an empty list if no events are available for the given day
      return [];
    }
  }

  Future<List<Evenement>> _getEventsForRange(
      DateTime start, DateTime end) async {
    final days = daysInRange(start, end);

    List<Evenement> events = [];
    for (final d in days) {
      List<Evenement> dayEvents = await _getEventsForDay(d);
      events.addAll(dayEvents);
    }
    return events;
  }

  void _onDaySelected(DateTime selectedDay, DateTime focusedDay) {
    if (!isSameDay(_selectedDay, selectedDay)) {
      setState(() {
        _selectedDay = selectedDay;
        _focusedDay = focusedDay;
        _rangeStart = null; // Important to clean those
        _rangeEnd = null;
        _rangeSelectionMode = RangeSelectionMode.toggledOff;
      });

      _selectedEvents.value = _getEventsForDay(selectedDay) as List<Evenement>;
    }
  }

  void _onRangeSelected(DateTime? start, DateTime? end, DateTime focusedDay) {
    setState(() {
      _selectedDay = null;
      _focusedDay = focusedDay;
      _rangeStart = start;
      _rangeEnd = end;
      _rangeSelectionMode = RangeSelectionMode.toggledOn;
    });

    // `start` or `end` could be null
    if (start != null && end != null) {
      _selectedEvents.value = _getEventsForRange(start, end) as List<Evenement>;
    } else if (start != null) {
      _selectedEvents.value = _getEventsForDay(start) as List<Evenement>;
    } else if (end != null) {
      _selectedEvents.value = _getEventsForDay(end) as List<Evenement>;
    }
  }

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Scaffold(
        // App bar ///////////////////////////////////////////////////////////
        appBar: MonAppBar(
          myTitle: 'Mes interventions PEPS',
        ),
        drawer: const MyNavDrawer(),

        body: Center(
          child: Padding(
            padding: const EdgeInsets.only(
              left: 15.0,
              top: 5.0,
              right: 15.0,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                ElevatedButton(
                    onPressed: () => Navigator.push(
                        context,
                        MaterialPageRoute(
                            builder: (context) => const ListeSeanceList())),
                    style: ElevatedButton.styleFrom(
                      minimumSize: const Size.fromHeight(40),
                    ),
                    child: const Text("Liste des séances à venir")),
                TableCalendar<Evenement>(
                  firstDay: kFirstDay,
                  lastDay: kLastDay,
                  focusedDay: _focusedDay,
                  selectedDayPredicate: (day) => isSameDay(_selectedDay, day),
                  rangeStartDay: _rangeStart,
                  rangeEndDay: _rangeEnd,
                  calendarFormat: _calendarFormat,
                  rangeSelectionMode: _rangeSelectionMode,
                  eventLoader: eventsForDay,
                  startingDayOfWeek: StartingDayOfWeek.monday,
                  calendarStyle: const CalendarStyle(
                    // Use `CalendarStyle` to customize the UI
                    outsideDaysVisible: false,
                  ),
                  onDaySelected: _onDaySelected,
                  onRangeSelected: _onRangeSelected,
                  onFormatChanged: (format) {
                    if (_calendarFormat != format) {
                      setState(() {
                        _calendarFormat = format;
                      });
                    }
                  },
                  onPageChanged: (focusedDay) {
                    _focusedDay = focusedDay;
                  },
                  locale: "fr_FR",
                  headerStyle: const HeaderStyle(titleCentered: true),
                ),
                const SizedBox(height: 8.0),
                Expanded(
                  child: ValueListenableBuilder<List<Evenement>>(
                    valueListenable: _selectedEvents,
                    builder: (context, value, _) {
                      return ListView.builder(
                        itemCount: value.length,
                        itemBuilder: (context, index) {
                          return Container(
                            margin: const EdgeInsets.symmetric(
                              horizontal: 12.0,
                              vertical: 4.0,
                            ),
                            decoration: BoxDecoration(
                              border: Border.all(),
                              borderRadius: BorderRadius.circular(12.0),
                            ),
                            child: ListTile(
                              onTap: () => Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => const MaSeance(),
                                  )),
                              title: Text(
                                  '${value[index]} ${value[index].fromTimeToString()}-${value[index].toTimeToString()}'),
                            ),
                          );
                        },
                      );
                    },
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
