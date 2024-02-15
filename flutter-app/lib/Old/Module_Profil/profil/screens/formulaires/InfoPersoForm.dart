import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class InfoPersoForm extends StatefulWidget {

  const InfoPersoForm({Key? key}) : super(key: key);

  @override
  _InfoPersoFormState createState() => _InfoPersoFormState();
}

class _InfoPersoFormState extends State<InfoPersoForm> {
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();

  final bool _infoP = true;

  String _prenom = "Kirua";
  String _nom = "Zoldick";
 // String _date = "07/12/2009";
  String _email = "kz@gmail.com";
  String _tel = "0102030405";
  final TextEditingController _dateinput = TextEditingController();

  final RegExp _prenomNomRegex = RegExp(r"(?:^[A-Z]([a-z]+)$|^(^[A-Z][a-z]+)\-([A-Z]([a-z]+)$)$)");
  final RegExp _dateRegex = RegExp(r"^([0-2][1-9]|3[0-1])\/(0[1-9]|1[0-2])\/(19|20)[0-9]{2}$");
  final RegExp _emailRegex = RegExp(r"[a-z0-9\._-]+@[a-z0-9\._-]+\.[a-z]+");
  final RegExp _telRegex = RegExp(r"^(?:(?:\+|00)33[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$");


  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Scaffold(
        //////////////////////////////////////////////////////////////////////
        // App bar ///////////////////////////////////////////////////////////
        appBar: AppBar(
          elevation: 0.0,
          backgroundColor: Theme.of(context).primaryColor,
          title: const Text('Mon Profil'),
          // leading: IconButton(
          //   icon: Icon(Icons.menu_rounded),
          //   color: Theme.of(context).scaffoldBackgroundColor,
          //   onPressed: () => widget.onChangedStep(0), // TODO
          // ),
          // actions: [
          //   IconButton(
          //     icon: Icon(Icons.power_settings_new_rounded),
          //     color: Colors.teal,
          //     onPressed: () => {}, // TODO
          //   ),
          // ]
        ),

        body:Padding(
          padding: const EdgeInsets.only(
            left: 25.0,
            right: 25.0,
            top: 15.0,
            bottom: 15.0,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch, // étire sur tt l'écran
            children: [

              //////////////////////////////////////////////////////////////////
              // Titre /////////////////////////////////////////////////////////
              Container(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    RichText(
                      text: TextSpan(
                        text: "Informations Personnelles".toUpperCase(),
                        style: const TextStyle(
                          color: Color(0xFF001e1b),
                          fontSize: 26.0,
                        ),
                      ),
                    ),

                    const Divider(
                      color: Colors.teal,
                      thickness: 1.5,
                      indent: 10,
                      endIndent: 50,
                    ),

                    const SizedBox(
                      height: 15.0,
                    ),
                  ],
                ),
              ),

              //////////////////////////////////////////////////////////////////
              // Scroll view ///////////////////////////////////////////////////
              Expanded(
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [

                      ///////////////////////////////////////////////////
                      // Formulaire /////////////////////////////////////
                      Form(
                        key: _formKey,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [

                            /////////////////////////////////////
                            // Prénom ///////////////////////////
                            Container(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  const Text(
                                    'Prénom',
                                    style: TextStyle(
                                      fontSize: 18.0,
                                    ),
                                  ),

                                  const SizedBox(
                                    height: 10.0,
                                  ),

                                  TextFormField(
                                    onChanged: (value) => setState(() => _prenom = value),
                                    validator: (value) =>
                                    value!.isEmpty || !_prenomNomRegex.hasMatch(value)
                                        ? "Entrez un prénom valide"
                                        : null,
                                    initialValue:
                                    _infoP
                                        ? _prenom
                                        : null,
                                    decoration: InputDecoration(
                                      hintText: "Prénom",
                                      contentPadding: const EdgeInsets.symmetric(
                                          horizontal: 10.0
                                      ),
                                      border: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(5.0),
                                        borderSide: const BorderSide(
                                          color: Colors.grey,
                                        ),
                                      ),
                                      focusedBorder: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(5.0),
                                        borderSide: const BorderSide(
                                          color: Colors.grey,
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),

                            const SizedBox(
                              height: 30.0,
                            ),

                            /////////////////////////////////////
                            // Nom //////////////////////////////
                            Container(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  const Text(
                                    'Nom',
                                    style: TextStyle(
                                      fontSize: 18.0,
                                    ),
                                  ),

                                  const SizedBox(
                                    height: 10.0,
                                  ),

                                  TextFormField(
                                    onChanged: (value) => setState(() => _nom = value),
                                    validator: (value) =>
                                    value!.isEmpty || !_prenomNomRegex.hasMatch(value)
                                        ? "Entrez un nom valide"
                                        : null,
                                    initialValue:
                                    _infoP
                                        ? _nom
                                        : null,
                                    decoration: InputDecoration(
                                      hintText: "Nom",
                                      contentPadding: const EdgeInsets.symmetric(
                                          horizontal: 10.0
                                      ),
                                      border: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(5.0),
                                        borderSide: const BorderSide(
                                          color: Colors.grey,
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),

                            const SizedBox(
                              height: 30.0,
                            ),

                            /////////////////////////////////////
                            // Date de naissance ////////////////
                            Container(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  const Text(
                                    'Date de Naissance',
                                    style: TextStyle(
                                      fontSize: 18.0,
                                    ),
                                  ),

                                  const SizedBox(
                                    height: 10.0,
                                  ),

                                  TextFormField(
                                    onChanged: (value) => setState(() => _dateinput.text = value),
                                    validator: (value) =>
                                    value!.isEmpty || !_dateRegex.hasMatch(value)
                                        ? "Please enter a valid date"
                                        : null,
                                    // initialValue:
                                    //   _infoP
                                    //     ? _dateinput.text
                                    //     : null,
                                    controller: _dateinput,
                                    decoration: InputDecoration(
                                      hintText: "jj/mm/aaaa",
                                      contentPadding: const EdgeInsets.symmetric(
                                          horizontal: 10.0
                                      ),
                                      border: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(5.0),
                                        borderSide: const BorderSide(
                                          color: Colors.grey,
                                        ),
                                      ),
                                      suffixIcon: const InkWell(
                                        child: Icon(Icons.calendar_today),
                                      ),
                                    ),
                                    readOnly: true,
                                    onTap: () async {
                                      DateTime? pickedDate = await showDatePicker(
                                          context: context,
                                          locale : const Locale("fr","FR"),
                                          initialDate: DateTime.now(),
                                          firstDate: DateTime(1900), //DateTime.now() - not to allow to choose before today.
                                          lastDate: DateTime.now()
                                      );
                                      if (pickedDate != null) {
                                        String formattedDate = DateFormat('dd/MM/yyyy').format(pickedDate);
                                        //you can implement different kind of Date Format here according to your requirement

                                        setState(() {
                                          _dateinput.text = formattedDate; //set output date to TextField value.
                                        });
                                      }
                                      else {
                                        print("Date non sélectionnée");
                                      }
                                    },
                                  ),
                                ],
                              ),
                            ),

                            const SizedBox(
                              height: 30.0,
                            ),

                            /////////////////////////////////////
                            // Email ////////////////////////////
                            Container(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  const Text(
                                    'Email',
                                    style: TextStyle(
                                      fontSize: 18.0,
                                    ),
                                  ),

                                  const SizedBox(
                                    height: 10.0,
                                  ),

                                  TextFormField(
                                    onChanged: (value) => setState(() => _email = value),
                                    validator: (value) =>
                                    value!.isEmpty || !_emailRegex.hasMatch(value)
                                        ? "Entrez un email valide"
                                        : null,
                                    initialValue:
                                    _infoP
                                        ? _email
                                        : null,
                                    decoration: InputDecoration(
                                      hintText: "Email",
                                      contentPadding: const EdgeInsets.symmetric(
                                          horizontal: 10.0
                                      ),
                                      border: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(5.0),
                                        borderSide: const BorderSide(
                                          color: Colors.grey,
                                        ),
                                      ),
                                      suffixIcon: const InkWell(
                                        child: Icon(Icons.email_rounded),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),

                            const SizedBox(
                              height: 30.0,
                            ),

                            /////////////////////////////////////
                            // Téléphone ////////////////////////
                            Container(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  const Text(
                                    'Téléphone',
                                    style: TextStyle(
                                      fontSize: 18.0,
                                    ),
                                  ),

                                  const SizedBox(
                                    height: 10.0,
                                  ),

                                  TextFormField(
                                    onChanged: (value) => setState(() => _tel = value),
                                    validator: (value) =>
                                    value!.isEmpty || !_telRegex.hasMatch(value)
                                        ? "Entrez un numéro de téléphone valide"
                                        : null,
                                    initialValue:
                                    _infoP
                                        ? _tel
                                        : null,
                                    decoration: InputDecoration(
                                      hintText: "Téléphone",
                                      contentPadding: const EdgeInsets.symmetric(
                                          horizontal: 10.0
                                      ),
                                      border: OutlineInputBorder(
                                        borderRadius: BorderRadius.circular(5.0),
                                        borderSide: const BorderSide(
                                          color: Colors.grey,
                                        ),
                                      ),
                                      suffixIcon: const InkWell(
                                        child: Icon(Icons.phone),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            )
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(
                height: 15.0,
              ),

              /////////////////////////////////////////////
              // Bouton sauvegarder ///////////////////////
/*
              RaisedButton(
                color: Theme.of(context).primaryColor,
                elevation: 0,
                padding: EdgeInsets.symmetric(
                  vertical: 15.0,
                ),
                onPressed:
                !_prenomNomRegex.hasMatch(_prenom) || !_prenomNomRegex.hasMatch(_nom) || (_dateinput.text).isEmpty || !_emailRegex.hasMatch(_email) || !_telRegex.hasMatch(_tel)
                    ? null
                    : () => {
                  if(_formKey.currentState!.validate()) {
                  }
                },
                child: Text(
                  'sauvegarder'.toUpperCase(),
                  style: TextStyle(
                    color: Colors.white,
                  ),
                ),
              ),
*/
            ],
          ),
        ),

        /*  bottomNavigationBar: BottomAppBar(
          color: Theme.of(context).scaffoldBackgroundColor,
          child: Container (
            padding: EdgeInsets.all(10.0),
            child: Row(
              children:[
                Expanded(
                  child:
                ),
              ]
            ),
          )
        ),*/
      ),
    );
  }
}
