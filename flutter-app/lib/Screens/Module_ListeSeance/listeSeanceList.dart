import 'package:flutter/material.dart';
import 'package:flutter_application_3/Screens/Components/maNavDrawer.dart';
import 'package:flutter_application_3/Screens/Components/monAppBar.dart';
import 'package:flutter_application_3/Screens/Module_ListeSeance/ListeSeance_Utils/evenement.dart';
import 'package:syncfusion_flutter_calendar/calendar.dart';
import 'package:flutter_application_3/Screens/Module_ListeSeance/listeSeanceCal.dart';

import '../Module_DetailsSeance/maSeanceDetail.dart';

class ListeSeanceList extends StatefulWidget {
  const ListeSeanceList({Key? key}) : super(key: key);

  @override
  _ListeSeanceListState createState() => _ListeSeanceListState();
}

class _ListeSeanceListState extends State<ListeSeanceList> {
  void calendarTapped(CalendarTapDetails detail) {
    if (detail.targetElement == CalendarElement.appointment) {
      final Appointment appointmentDetails = detail.appointments![0];

      Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => const MaSeance(),
          )
      );
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
                    children: [
                      ElevatedButton(
                          onPressed: () => Navigator.push(
                              context,
                              MaterialPageRoute(
                                  builder: (context) =>
                                      const ListeSeanceCal())),
                          style: ElevatedButton.styleFrom(
                            minimumSize: const Size.fromHeight(40),
                          ),
                          child: const Text("Calendrier des séances à venir")),
                      Expanded(
                          child: SfCalendar(
                            view: CalendarView.schedule,
                            scheduleViewSettings:
                            ScheduleViewSettings(
                              hideEmptyScheduleWeek: true,
                              monthHeaderSettings: MonthHeaderSettings(
                                backgroundColor: Theme.of(context).primaryColor,
                              ),
                              appointmentTextStyle: const TextStyle(
                                color: Colors.black
                              )
                            ),
                            dataSource: MeetingDataSource(sampleData),
                            onTap: calendarTapped,
                          ),
                      )
                    ],
                  )
              )
          )
      ),
    );
  }
}
