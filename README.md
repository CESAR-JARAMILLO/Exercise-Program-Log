# Program Log

A Laravel-based training program management application that allows users to create, manage, and track their workout programs with a hierarchical structure of weeks, days, and exercises.

## Overview

Program Log is a web application built with Laravel and Livewire Volt that enables users to:
- Create comprehensive training programs with multiple weeks
- Organize workouts by days within each week
- Add detailed exercises with various metrics (sets, reps, weight, distance, time)
- View, edit, and delete their programs
- Track program start and end dates

## Features

### Current Features
- ✅ **User Authentication** - Secure user registration and login
- ✅ **Program Management** - Full CRUD operations for training programs
- ✅ **Hierarchical Structure** - Programs → Weeks → Days → Exercises
- ✅ **Exercise Details** - Track sets, reps, weight, distance, and time
- ✅ **Date Management** - Set start and end dates for programs
- ✅ **Collapsible UI** - Expandable/collapsible weeks for better navigation
- ✅ **Flash Messages** - User-friendly success and error notifications
- ✅ **Authorization** - Users can only access and modify their own programs

### Program Structure
- **Program**: Top-level container with name, description, dates, and notes
- **Weeks**: Multiple weeks per program (1-52 weeks)
- **Days**: 7 days per week (Day 1-7)
- **Exercises**: Multiple exercises per day with detailed metrics

## Database Structure

### Tables

#### `programs`
Stores the main program information:
- `id` - Primary key
- `user_id` - Foreign key to users table
- `name` - Program name
- `description` - Program description
- `length_weeks` - Number of weeks in the program
- `start_date` - Program start date (cast to Carbon date)
- `end_date` - Program end date (cast to Carbon date)
- `notes` - Additional notes
- `created_at`, `updated_at` - Timestamps

#### `program_weeks`
Stores weeks within a program:
- `id` - Primary key
- `program_id` - Foreign key to programs table
- `week_number` - Week number (1, 2, 3, etc.)
- `created_at`, `updated_at` - Timestamps

#### `program_days`
Stores days within a week:
- `id` - Primary key
- `program_week_id` - Foreign key to program_weeks table
- `day_number` - Day number (1-7)
- `label` - Custom day label (e.g., "Push Day", "Pull Day")
- `created_at`, `updated_at` - Timestamps

#### `day_exercises`
Stores exercises for each day:
- `id` - Primary key
- `program_day_id` - Foreign key to program_days table
- `name` - Exercise name
- `type` - Exercise type (strength, cardio, flexibility, other)
- `sets` - Number of sets
- `reps` - Number of repetitions
- `weight` - Weight in pounds
- `distance` - Distance in miles
- `time_seconds` - Time in seconds
- `order` - Display order
- `created_at`, `updated_at` - Timestamps

### Relationships

```
User
  └── hasMany → Program
        └── hasMany → ProgramWeek
              └── hasMany → ProgramDay
                    └── hasMany → DayExercise
```

## Key Files and Their Purposes

### Models

#### `app/Models/Program.php`
- Main program model
- Defines relationships with User and ProgramWeek
- Includes date casting for `start_date` and `end_date`
- Fillable fields for mass assignment protection

#### `app/Models/ProgramWeek.php`
- Represents a week within a program
- Belongs to Program, has many ProgramDays
- Tracks week number

#### `app/Models/ProgramDay.php`
- Represents a day within a week
- Belongs to ProgramWeek, has many DayExercises
- Stores day number and optional label

#### `app/Models/DayExercise.php`
- Represents an exercise within a day
- Belongs to ProgramDay
- Stores all exercise metrics (sets, reps, weight, distance, time)
- Includes order field for sorting

### Migrations

#### `database/migrations/2025_11_21_174421_create_programs_table.php`
- Creates the main programs table
- Sets up foreign key relationship with users

#### `database/migrations/2025_11_22_180201_add_length_weeks_to_programs_table.php`
- Adds `length_weeks` column to programs table

#### `database/migrations/2025_11_22_180511_create_program_weeks_table.php`
- Creates program_weeks table
- Links weeks to programs

#### `database/migrations/2025_11_22_181001_create_program_days_table.php`
- Creates program_days table
- Links days to weeks

#### `database/migrations/2025_11_22_182412_create_day_exercises_table.php`
- Creates day_exercises table
- Links exercises to days
- Stores all exercise metrics

#### `database/migrations/2025_11_22_183249_drop_exercises_table.php`
- Drops the old standalone exercises table (replaced by day_exercises)

### Views (Livewire Volt Components)

#### `resources/views/livewire/programs/index.blade.php`
- **Purpose**: Lists all programs for the authenticated user
- **Features**:
  - Displays program cards with key information
  - Shows program name, description, length, and dates
  - Provides View, Edit, and Delete actions
  - Flash message display for success/error notifications
- **Route**: `GET /programs`

#### `resources/views/livewire/programs/create.blade.php`
- **Purpose**: Form to create a new program with all weeks, days, and exercises
- **Features**:
  - Single-page form for complete program creation
  - Dynamic week generation based on length
  - Collapsible weeks for better UX
  - Add/remove exercises per day
  - Validation for all fields
  - Flash messages for success/error
- **Route**: `GET /programs/create`, `POST` via Livewire

#### `resources/views/livewire/programs/show.blade.php`
- **Purpose**: Display detailed view of a single program
- **Features**:
  - Shows all program information
  - Displays weeks, days, and exercises in hierarchical structure
  - Collapsible weeks
  - Edit and Delete actions
  - Authorization check (users can only view their own programs)
- **Route**: `GET /programs/{program}`

#### `resources/views/livewire/programs/edit.blade.php`
- **Purpose**: Form to edit an existing program
- **Features**:
  - Pre-populates all existing data
  - Allows modification of weeks, days, and exercises
  - Smart update logic (updates existing, creates new, deletes removed)
  - Can add/remove weeks dynamically
  - Validation and flash messages
- **Route**: `GET /programs/{program}/edit`, `PUT` via Livewire

### Routes

#### `routes/web.php`
Defines all application routes:
- **Public Routes**:
  - `GET /` - Welcome page
- **Authenticated Routes**:
  - `GET /dashboard` - Dashboard
  - `GET /programs` - List programs
  - `GET /programs/create` - Create program form
  - `GET /programs/{program}` - View program
  - `GET /programs/{program}/edit` - Edit program form
  - Settings routes (profile, password, appearance, two-factor)

All program routes use Livewire Volt components and require authentication.

## Setup Instructions

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and NPM
- MySQL/PostgreSQL database

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
   - Start date (optional)
   - End date (optional)
   - Notes (optional)
3. Once length is set, weeks will appear
4. For each week, expand to see days
5. For each day, click "Add Exercise" to add exercises
6. Fill in exercise details:
   - Exercise name (required)
   - Type (strength, cardio, flexibility, other)
   - Sets, reps, weight, distance, time (optional)
7. Click "Create Complete Program" to save

### Viewing a Program

1. From the programs list, click "View" on any program
2. All weeks are expanded by default
3. Click on week headers to collapse/expand
4. View all exercises with their details

### Editing a Program

1. From the programs list or view page, click "Edit"
2. Modify any program details
3. Add or remove exercises
4. Change the number of weeks (adds/removes weeks as needed)
5. Click "Update Program" to save changes

### Deleting a Program

1. From the programs list or view page, click "Delete"
2. Confirm the deletion
3. Program and all associated data will be permanently deleted

## Technology Stack

- **Backend**: Laravel 11
- **Frontend**: Livewire Volt, Alpine.js
- **UI Components**: Flux UI
- **Database**: MySQL/PostgreSQL
- **Authentication**: Laravel Fortify

## Security Features

- User authentication required for all program operations
- Authorization checks ensure users can only access their own programs
- CSRF protection on all forms
- Input validation on all user inputs
- SQL injection protection via Eloquent ORM

## Future Enhancements

Potential features for future development:
- Program duplication/cloning
- Search and filter functionality
- Custom day labels editing
- Progress tracking (mark exercises as completed)
- Export/import programs
- Program templates
- Workout history tracking

## License

[Specify your license here]

## Contributing

[Add contribution guidelines if applicable]

## Support

[Add support information if applicable]

