/*
import 'package:flutter/material.dart';
import 'package:flutter_application_3/Module_Suivi_PEPS/Calendar/Model/event_data_source.dart';
import 'package:flutter_application_3/Module_Suivi_PEPS/Calendar/Provider/event_provider.dart';
import 'package:flutter_application_3/Module_Suivi_PEPS/Calendar/Widget/tasks_widget.dart';
import 'package:path_provider/path_provider.dart';
import 'package:provider/provider.dart';
import 'package:syncfusion_flutter_calendar/calendar.dart';
*
class CalendarWidget extends StatelessWidget {
  const CalendarWidget({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final events = Provider.of<EventProvider>(context).events;

    return SfCalendar(
      view: CalendarView.month,
      dataSource: EventDataSource(events),
      initialSelectedDate: DateTime.now(),
      cellBorderColor: Colors.transparent,
      onLongPress: (details) {
        final provider = Provider.of<EventProvider>(context, listen: true);
        provider.setDate(details.date!);
        showModalBottomSheet(
          context: context,
          builder: (context) => TasksWidget(),
        );
      },
    );
  }
}
*/
