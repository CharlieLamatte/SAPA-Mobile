import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/material.dart';

class MotivationCarousel extends StatelessWidget {
  final _images = [
    "motivation1.jpg",
    "motivation2.jpg",
    "motivation3.png",
    "motivation4.jpg",
    "motivation5.png",
    "motivation6.jpg",
    "motivation7.png",
    "motivation8.jpg",
    "motivation9.png",
    "motivation10.jpg",
    "motivation11.jpg",
    "motivation12.png",
  ];

  const MotivationCarousel({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return CarouselSlider(
        items: _images.map<Widget>((img) {
          return Container(
            padding: const EdgeInsets.only(right: 0),
            child: SizedBox(
              child: Card(
                elevation: 2,
                child: Image(
                  image: AssetImage("assets/carrouselHome/$img"),
                  fit: BoxFit.fitWidth,
                ),
              ),
            ),
          );
        }).toList(),
        options: CarouselOptions(
          autoPlay: true,
          enableInfiniteScroll: true,
          initialPage: 1,
          height: 150,
        ));
  }
}
