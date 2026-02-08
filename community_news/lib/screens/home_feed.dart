import 'package:flutter/material.dart';
import '../widgets/news_card.dart';

class HomeFeed extends StatelessWidget {
  const HomeFeed({Key? key}) : super(key: key);

  final List<Map<String, String>> news = const [
    {
      'imageUrl': 'https://picsum.photos/id/1018/400/200',
      'headline': 'Local Community Garden Opens This Weekend!',
      'description': 'Join us for the grand opening of the new community garden...',
    },
    {
      'imageUrl': 'https://picsum.photos/id/1025/400/200',
      'headline': 'Upcoming Charity Run for Local Schools',
      'description': 'Register now for the 5K run to support education in our district.',
    },
    {
      'imageUrl': 'https://picsum.photos/id/1036/400/200',
      'headline': 'New Library Hours Announced',
      'description': 'Starting next month, the library will be open on Sundays.',
    },
  ];

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      padding: const EdgeInsets.all(16.0),
      itemCount: news.length,
      itemBuilder: (context, index) {
        final item = news[index];
        return NewsCard(
          imageUrl: item['imageUrl']!,
          headline: item['headline']!,
          onShare: () {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Sharing functionality to be implemented')),
            );
          },
          onReadMore: () {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Read More functionality to be implemented')),
            );
          },
        );
      },
    );
  }
}
