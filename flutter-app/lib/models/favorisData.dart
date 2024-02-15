import 'package:meta/meta.dart';

@immutable
class FavorisData {
  const FavorisData({
    required this.name,
    required this.url,
  });

  final String name;
  final String url;
}