# Program Log

A Laravel-based fitness program management platform that enables users to create, manage, and track workout programs. The app supports both individual users and trainer-client relationships with subscription-based feature tiers.

## Overview

Program Log is a web application built with Laravel and Livewire Volt that enables users to:

-   Create comprehensive training programs with multiple weeks
-   Organize workouts by days within each week
-   Add detailed exercises with various metrics (sets, reps, weight, distance, time)
-   Track active programs and log workouts
-   View statistics and analytics
-   Connect trainers with clients for program sharing and progress tracking

## Features

### User Authentication & Settings

-   ✅ Secure registration with strong password requirements
-   ✅ Login/logout with enhanced error handling
-   ✅ Password reset flow
-   ✅ Two-factor authentication (2FA)
-   ✅ User profile management
-   ✅ Timezone settings
-   ✅ Appearance settings (light/dark mode)

### Program Management

-   ✅ **Create Programs**: Multi-week programs with customizable structure
-   ✅ **Program Structure**: Programs → Weeks (1-52) → Days (1-7) → Exercises
-   ✅ **Exercise Details**: Track sets, reps, weight, distance, time, exercise type
-   ✅ **View/Edit/Delete**: Full CRUD operations with authorization
-   ✅ **Program Limits**: Tier-based program creation limits
-   ✅ **Collapsible UI**: Expandable/collapsible weeks for better navigation
-   ✅ **Copy Features**: Copy day and copy week functionality in edit form
-   ✅ **Program Templates**: Programs remain as templates so multiple users can start the same program
-   ✅ **Individual Progress**: Each user's progress tracked separately via ActiveProgram instances

### Active Programs & Workout Logging

-   ✅ Start programs to track active workouts
-   ✅ Calendar view for scheduled workouts (filtered by selected program)
-   ✅ Program-specific calendar routes (`/workouts/{program}`)
-   ✅ Log workouts with exercise details (exercise name auto-filled, not editable)
-   ✅ Workout history tracking
-   ✅ Stop/restart active programs
-   ✅ Completion rate tracking
-   ✅ Multiple users can start the same program template independently

### Statistics & Analytics

-   ✅ Personal workout statistics
-   ✅ Workout frequency tracking (weekly/monthly)
-   ✅ Completion rates
-   ✅ Active programs overview
-   ✅ Exercise performance metrics

### Mobile Responsiveness

-   ✅ Fully responsive design across all pages
-   ✅ Mobile-optimized dashboard with centered headers and responsive cards
-   ✅ Touch-friendly calendar view with mobile list layout
-   ✅ Responsive program creation and editing forms
-   ✅ Mobile-friendly workout logging and history
-   ✅ Scrollable charts and graphs on mobile devices
-   ✅ Optimized trainer analytics views for mobile screens

### Trainer-Client Features

-   ✅ **Trainer Requests**: Trainers can send connection requests to clients
-   ✅ **Client Management**: Trainers can view and manage their clients
-   ✅ **Program Assignment**: Trainers can assign programs to clients (from program show page and index)
-   ✅ **Client Count Badges**: Trainers see active/assigned client counts on program cards
-   ✅ **Client Analytics**: Trainers can view detailed analytics for each client
-   ✅ **In-App Notifications**: Real-time notifications for requests and assignments
-   ✅ **Program Sharing**: Trainers can assign template programs to multiple clients

### Subscription Tiers

-   **Free**: 1 program, basic features
-   **Basic**: 5 programs, enhanced analytics
-   **Trainer**: 20 programs, client management, program sharing
-   **Pro Trainer**: 50 programs, unlimited clients, advanced analytics

### Notifications System

-   ✅ In-app notification center
-   ✅ Notifications for trainer requests (sent/accepted)
-   ✅ Notifications for program assignments
-   ✅ Mark as read/unread functionality
-   ✅ Notification badges in sidebar

## Program Structure

-   **Program**: Top-level container with name, description, dates, and notes
-   **Weeks**: Multiple weeks per program (1-52 weeks)
-   **Days**: 7 days per week (Day 1-7)
-   **Exercises**: Multiple exercises per day with detailed metrics

## Database Structure

### Key Tables

#### `programs`

-   `id`, `user_id`, `trainer_id`, `name`, `description`, `length_weeks`, `start_date`, `end_date`, `notes`, `status`, `created_at`, `updated_at`
-   **Status**: Always `'template'` - programs are templates that multiple users can start independently

#### `program_weeks`

-   `id`, `program_id`, `week_number`, `created_at`, `updated_at`

#### `program_days`

-   `id`, `program_week_id`, `day_number`, `label`, `created_at`, `updated_at`

#### `day_exercises`

-   `id`, `program_day_id`, `name`, `type`, `sets`, `reps`, `weight`, `distance`, `time_seconds`, `order`, `created_at`, `updated_at`

#### `active_programs`

-   Tracks when users start programs and their individual progress
-   `id`, `user_id`, `program_id`, `started_at`, `current_week`, `current_day`, `status`, `stopped_at`, `created_at`, `updated_at`
-   **Status**: `'active'`, `'stopped'`, or `'completed'` - tracks individual user's program instance
-   Each user gets their own ActiveProgram instance when starting a program template

#### `workout_logs` / `workout_exercises`

-   Records completed workouts and exercise performance

#### `trainer_client_relationships`

-   Manages trainer-client connections with status tracking

#### `program_assignments`

-   Links trainers to clients for specific programs

### Relationships

```
User
  ├── hasMany → Program
  │     └── hasMany → ProgramWeek
  │           └── hasMany → ProgramDay
  │                 └── hasMany → DayExercise
  ├── hasMany → ActiveProgram
  ├── hasMany → WorkoutLog
  ├── trainerRelationships → TrainerClientRelationship
  └── clientRelationships → TrainerClientRelationship
```

## Key Files

### Models

-   `app/Models/User.php` - User model with subscription tier and trainer-client relationships
-   `app/Models/Program.php` - Program model with authorization methods
-   `app/Models/ActiveProgram.php` - Tracks active program instances
-   `app/Models/WorkoutLog.php` - Workout logging
-   `app/Models/TrainerClientRelationship.php` - Trainer-client connections
-   `app/Models/ProgramAssignment.php` - Program assignments

### Services

-   `app/Services/TrainerAnalyticsService.php` - Aggregates client analytics, completion rates, workout stats

### Notifications

-   `app/Notifications/TrainerRequestSent.php` - Notification when trainer sends request
-   `app/Notifications/TrainerRequestAccepted.php` - Notification when client accepts
-   `app/Notifications/ProgramAssigned.php` - Notification when program is assigned

### Livewire Components

All major features use Livewire Volt for reactive UI:

-   `resources/views/livewire/programs/` - Program management
-   `resources/views/livewire/workouts/` - Workout logging
-   `resources/views/livewire/trainers/` - Trainer features
-   `resources/views/livewire/statistics/` - Statistics and analytics
-   `resources/views/livewire/notifications/` - Notification center

## Routes

### Public

-   `GET /` - Landing page with pricing

### Authenticated

-   `GET /dashboard` - User dashboard
-   `GET /programs` - List all programs
-   `GET /programs/create` - Create new program
-   `GET /programs/{program}` - View program details
-   `GET /programs/{program}/edit` - Edit program
-   `GET /programs/{program}/start` - Start a program
-   `GET /programs/{program}/assign` - Assign program to clients (trainer only)
-   `GET /workouts` - Workout calendar (all programs)
-   `GET /workouts/{program}` - Workout calendar filtered by specific program
-   `GET /workouts/log/{activeProgram}/{date}` - Log workout
-   `GET /workouts/history` - Workout history
-   `GET /statistics` - Personal statistics
-   `GET /notifications` - Notification center
-   `GET /trainers/clients` - Trainer client management
-   `GET /trainers/analytics` - Trainer analytics dashboard
-   `GET /clients/requests` - Client request management
-   `GET /subscriptions/plans` - Subscription plans

### Settings

-   `GET /settings/profile` - Edit profile
-   `GET /settings/password` - Change password
-   `GET /settings/appearance` - Appearance settings
-   `GET /settings/timezone` - Timezone settings
-   `GET /settings/two-factor` - 2FA management

## Setup Instructions

### Prerequisites

-   PHP 8.2 or higher
-   Composer
-   Node.js and NPM
-   MySQL/PostgreSQL database (or SQLite for development)

### Installation

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd program-log
    ```

2. **Install PHP dependencies**

    ```bash
    composer install
    ```

3. **Install Node dependencies**

    ```bash
    npm install
    ```

4. **Environment Configuration**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    Update `.env` with your database credentials:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=program_log
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

5. **Run Migrations**

    ```bash
    php artisan migrate
    ```

6. **Build Assets**

    ```bash
    npm run build
    ```

7. **Start Development Server**

    ```bash
    php artisan serve
    ```

8. **Access the Application**
    - Visit `http://localhost:8000`
    - Register a new account or login
    - Navigate to `/programs` to start creating programs

## Usage Guide

### Creating a Program

1. Navigate to `/programs` and click "Create Program"
2. Fill in program details:
    - Program name (required)
    - Description (optional)
    - Length in weeks (1-52)
    - Notes (optional)
    - **Note**: Start and end dates are set when you start the program, not when creating the template
3. Once length is set, weeks will appear
4. For each week, expand to see days
5. For each day, click "Add Exercise" to add exercises
6. Fill in exercise details:
    - Exercise name (required)
    - Type (strength, cardio, flexibility, other)
    - Sets, reps, weight, distance, time (optional)
7. Click "Create Complete Program" to save
8. **Editing Programs**: Use "Copy Day" or "Copy Week" features to duplicate exercises from other days/weeks

### Starting and Logging Workouts

1. From the program view, click "Start Program"
2. View your scheduled workouts in the Calendar (`/workouts`)
3. Click on a program card to view that program's calendar (`/workouts/{program}`)
4. The calendar filters to show only workouts for the selected program
5. Click on a workout day to log your exercises
6. Exercise names are auto-filled and cannot be changed during logging
7. Track sets, reps, weight, and other metrics
8. View your workout history and statistics

### Trainer Features

1. Upgrade to Trainer or Pro Trainer tier
2. Send connection requests to clients (`/trainers/clients`)
3. Assign programs to connected clients (from program show page or index page)
4. View client count badges on program cards showing active/assigned clients
5. View client analytics and progress (`/trainers/analytics`)
6. Programs remain as templates so trainers can assign the same program to multiple clients

## Technology Stack

-   **Backend**: Laravel 11
-   **Frontend**: Livewire Volt, Alpine.js
-   **UI Components**: Flux UI
-   **Styling**: Tailwind CSS with responsive breakpoints
-   **Database**: MySQL/PostgreSQL (SQLite for development)
-   **Authentication**: Laravel Fortify
-   **Testing**: Pest PHP

## Security Features

-   User authentication required for all program operations
-   Authorization checks ensure users can only access their own programs or assigned programs
-   Route model binding with 404 errors for invalid program IDs
-   CSRF protection on all forms
-   Input validation on all user inputs (min/max ranges for sets, reps, weight, etc.)
-   SQL injection protection via Eloquent ORM
-   Strong password requirements
-   Two-factor authentication support
-   Program templates ensure data isolation between users

## Testing

Run tests with:

```bash
php artisan test
```

Current test coverage includes:

-   Authentication flows (registration, login, password reset)
-   Service tests for TrainerAnalyticsService
-   Password validation tests

## Configuration

-   Subscription tiers defined in `config/subscription.php`
-   Fortify configuration for auth features in `config/fortify.php`
-   Environment-based settings in `.env`

## License

[Specify your license here]

## Contributing

[Add contribution guidelines if applicable]

## Support

[Add support information if applicable]
