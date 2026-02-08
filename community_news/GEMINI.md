# Community News App

This is a mobile application for a local community news feed, built with Flutter.

## Features

- **Home Feed**: Displays a scrollable list of news articles with images, headlines, share, and read more options.
- **Directory**: A searchable list of professional profiles with contact options.
- **Navigation**: Bottom navigation bar to switch between Home, Directory, Post Update, and Profile.
- **Theme**: Modern, clean UI with Deep Blue (#0052CC) primary color and Poppins typography.

## Tech Stack

- **Framework**: Flutter
- **Language**: Dart
- **State Management**: Local state (StatefulWidget)
- **Dependencies**:
  - `google_fonts`: For Poppins font.
  - `flutter_svg`: (Optional)

## Project Structure

- `lib/main.dart`: Entry point, theme configuration, and main navigation.
- `lib/screens/`: Contains screen widgets (HomeFeed, DirectoryPage).
- `lib/widgets/`: Contains reusable widgets (NewsCard, DirectoryItem).

## Getting Started

1. Ensure Flutter is installed.
2. Run `flutter pub get` to install dependencies.
3. Run `flutter run` to launch the app.
