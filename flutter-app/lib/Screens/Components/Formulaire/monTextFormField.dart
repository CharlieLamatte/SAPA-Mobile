import 'package:flutter/material.dart';

class MonTextFormField extends StatefulWidget {
  final String? myLabelTitle;
  String? myVar;
  final RegExp myRegex;
  final String? myValidation;
  final String? myHintText;
  final String? mySuffixText;
  final IconData? mySuffixIcon;

  MonTextFormField({
    Key? key,
    this.myLabelTitle,
    this.myVar,
    required this.myRegex,
    this.myValidation,
    this.myHintText,
    this.mySuffixText,
    this.mySuffixIcon
  }) : super(key: key);

  @override
  _MonTextFormFieldState createState() => _MonTextFormFieldState();
}

class _MonTextFormFieldState extends State<MonTextFormField> {
  @override
  Widget build(BuildContext context) {
    return Container(
      child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              widget.myLabelTitle.toString(),
              style: const TextStyle(
                fontSize: 18.0,
              ),
            ),

            const SizedBox(
              height: 10.0,
            ),

            TextFormField(
              onChanged: (value) => setState(() => widget.myVar = value),
              validator: (value) =>
              widget.myRegex != null
                  ? value!.isEmpty || !widget.myRegex.hasMatch(value) // TODO voir dans le cas où il n'y a pas de regex :/
                  ? widget.myValidation
                  : null
                  : null,

              initialValue: widget.myVar,
              decoration: InputDecoration(
                hintText: widget.myHintText,
                contentPadding: const EdgeInsets.symmetric(
                    horizontal: 10.0
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(5.0),
                  borderSide: BorderSide(
                    color: Theme.of(context).hoverColor,
                  ),
                ),
                suffixText: widget.mySuffixText,
                suffixIcon: InkWell(
                  child: Icon(widget.mySuffixIcon),
                ),
              ),
            ),
          ]
      ),
    );
  }
}
