import 'package:flutter/material.dart';
import '../widgets/directory_item.dart';

class DirectoryPage extends StatefulWidget {
  const DirectoryPage({Key? key}) : super(key: key);

  @override
  State<DirectoryPage> createState() => _DirectoryPageState();
}

class _DirectoryPageState extends State<DirectoryPage> {
  final List<Map<String, String>> profiles = const [
    {
      'name': 'Dr. Sarah Smith',
      'profession': 'Dentist',
      'avatarUrl': 'https://randomuser.me/api/portraits/women/44.jpg',
    },
    {
      'name': 'John Doe',
      'profession': 'Plumber',
      'avatarUrl': 'https://randomuser.me/api/portraits/men/32.jpg',
    },
    {
      'name': 'Emily Davis',
      'profession': 'Lawyer',
      'avatarUrl': 'https://randomuser.me/api/portraits/women/68.jpg',
    },
    {
      'name': 'Michael Brown',
      'profession': 'Electrician',
      'avatarUrl': 'https://randomuser.me/api/portraits/men/11.jpg',
    },
  ];

  List<Map<String, String>> filteredProfiles = [];
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    filteredProfiles = profiles;
    _searchController.addListener(_filterProfiles);
  }

  void _filterProfiles() {
    final query = _searchController.text.toLowerCase();
    setState(() {
      filteredProfiles = profiles.where((profile) {
        final name = profile['name']!.toLowerCase();
        final profession = profile['profession']!.toLowerCase();
        return name.contains(query) || profession.contains(query);
      }).toList();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Search Bar
        Padding(
          padding: const EdgeInsets.all(16.0),
          child: TextField(
            controller: _searchController,
            decoration: InputDecoration(
              hintText: 'Search profiles...',
              prefixIcon: const Icon(Icons.search),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide.none,
              ),
              filled: true,
              fillColor: Colors.grey[200],
              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            ),
          ),
        ),
        // List
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            itemCount: filteredProfiles.length,
            itemBuilder: (context, index) {
              final profile = filteredProfiles[index];
              return DirectoryItem(
                avatarUrl: profile['avatarUrl']!,
                name: profile['name']!,
                profession: profile['profession']!,
                onCall: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Calling ${profile['name']}...')),
                  );
                },
              );
            },
          ),
        ),
      ],
    );
  }
}
