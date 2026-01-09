# Mental Gym - Quick Start Guide

Get the Mental Gym app running in 3 simple steps.

## Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- npm

## Quick Setup

```bash
# 1. Install dependencies
composer install
npm install

# 2. Set up database (already has .env configured for SQLite)
php artisan migrate:fresh --seed

# 3. Run the app (need 2 terminals)
# Terminal 1:
php artisan serve

# Terminal 2:
npm run dev
```

## Access the App
Open your browser and go to: **http://localhost:8000**

That's it! You should see the Mental Gym interface with a random question.

## What You Should See
1. A clean page with "Mental Gym" header
2. One random question displayed
3. A large text area to write your response
4. After submitting, a star rating interface
5. An optional feedback field
6. A completion screen with "Do another" or "I'm done" options

## API Testing
You can also test the API directly:

```bash
# Get a random question
curl http://localhost:8000/api/question/random

# Submit a response
curl -X POST http://localhost:8000/api/response \
  -H "Content-Type: application/json" \
  -d '{
    "question_id": 1,
    "response_text": "My honest response",
    "rating": 5,
    "feedback_text": "Great question",
    "anonymous_session_id": "test-123"
  }'
```

## Troubleshooting

### Database not found
```bash
php artisan migrate:fresh --seed
```

### Port already in use
```bash
php artisan serve --port=8001
```
Then access at http://localhost:8001

### Frontend not loading
Make sure both servers are running (Laravel + Vite)

## Need More Info?
See the full [README.md](README.md) for complete documentation.
