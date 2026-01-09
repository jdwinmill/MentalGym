# Mental Gym

A clean, intentional web application for mental exercises. One question per visit, designed for 2-5 minutes of honest reflection.

## Features

- **No Login Required**: Anonymous, privacy-focused experience
- **One Question at a Time**: Random selection from curated questions
- **Smart Rotation**: Excludes your last 10 questions to keep it fresh
- **Quick Feedback**: Rate your experience and provide optional feedback
- **Intent-Based Questions**: Questions tagged by category (values, clarity, avoidance, leadership, identity)
- **Clean, Modern UI**: Built with React, TypeScript, and Tailwind CSS

## Tech Stack

### Backend
- **Laravel 11**: PHP framework for backend and API
- **SQLite**: Lightweight database for questions and responses
- **RESTful API**: Clean endpoints for question retrieval and response submission

### Frontend
- **React 19**: Modern UI library
- **TypeScript**: Type-safe development
- **Tailwind CSS 4**: Utility-first styling
- **Vite**: Fast build tool and dev server
- **Inertia.js**: Modern monolith approach

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm

### Setup Instructions

1. **Install PHP Dependencies**
   ```bash
   composer install
   ```

2. **Install Node Dependencies**
   ```bash
   npm install
   ```

3. **Environment Setup**
   The `.env` file is already configured for SQLite. No changes needed unless you want to customize.

4. **Generate Application Key** (if not already set)
   ```bash
   php artisan key:generate
   ```

5. **Run Database Migrations & Seed**
   ```bash
   php artisan migrate:fresh --seed
   ```
   This creates the database tables and seeds 10 thoughtful questions.

## Running the Application

You need to run two processes simultaneously:

### Terminal 1: Laravel Backend
```bash
php artisan serve
```
This starts the Laravel server at `http://localhost:8000`

### Terminal 2: Vite Dev Server
```bash
npm run dev
```
This compiles and hot-reloads your frontend assets.

### Access the App
Open your browser and navigate to:
```
http://localhost:8000
```

## Usage

1. **Answer a Question**: Read the random question and write your response honestly
2. **Submit**: Click submit when you're done writing
3. **Rate & Feedback**: Give a star rating (1-5) and optional feedback
4. **Finish**: Click finish to complete the session
5. **Do Another or Done**: Choose to answer another question or finish

## API Endpoints

### Get Random Question
```
GET /api/question/random
```
Returns a random active question, excluding the last 10 served to the session.

**Response:**
```json
{
  "question": {
    "id": 1,
    "text": "What belief are you defending that you're not sure is still true?",
    "intent_tag": "clarity",
    "active": true
  },
  "session_id": "uuid-string"
}
```

### Submit Response
```
POST /api/response
```

**Request Body:**
```json
{
  "question_id": 1,
  "response_text": "Your honest response...",
  "rating": 4,
  "feedback_text": "Optional feedback",
  "anonymous_session_id": "uuid-string"
}
```

**Response:**
```json
{
  "message": "Response saved successfully",
  "response": { ... }
}
```

## Database Schema

### Questions Table
- `id`: Primary key
- `text`: Question text
- `intent_tag`: Category (values, clarity, avoidance, leadership, identity)
- `active`: Boolean flag
- `created_at`, `updated_at`: Timestamps

### Responses Table
- `id`: Primary key
- `question_id`: Foreign key to questions
- `response_text`: User's response
- `rating`: Star rating (1-5, nullable)
- `feedback_text`: Optional feedback
- `anonymous_session_id`: Session identifier
- `timestamp`: Response timestamp
- `created_at`, `updated_at`: Timestamps

## Project Structure

```
MentalGym/
├── app/
│   ├── Http/Controllers/Api/
│   │   └── MentalGymController.php    # API endpoints
│   └── Models/
│       ├── Question.php                # Question model
│       └── Response.php                # Response model
├── database/
│   ├── migrations/                     # Database migrations
│   ├── seeders/
│   │   └── QuestionSeeder.php         # Sample questions
│   └── database.sqlite                 # SQLite database
├── resources/
│   ├── js/
│   │   └── pages/
│   │       └── mental-gym.tsx         # Main React component
│   └── views/
│       └── app.blade.php              # Base layout
├── routes/
│   ├── api.php                        # API routes
│   └── web.php                        # Web routes
└── config/
    └── cors.php                       # CORS configuration
```

## Development

### Adding New Questions
Edit `database/seeders/QuestionSeeder.php` and add new questions to the array, then run:
```bash
php artisan migrate:fresh --seed
```

### Building for Production
```bash
npm run build
php artisan optimize
```

### Code Quality
```bash
# Format code
npm run format

# Lint code
npm run lint

# Type check
npm run types
```

## Design Philosophy

- **Quiet & Intentional**: No distractions, no gamification, no stats
- **Privacy First**: No accounts, no tracking, anonymous sessions only
- **One Rep at a Time**: Focus on doing the mental work, one question at a time
- **Clean Aesthetics**: Airy, neutral design that doesn't get in the way

## License

This is a custom application. All rights reserved.
