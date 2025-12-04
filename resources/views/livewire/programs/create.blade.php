<?php

use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\ProgramDay;
use App\Models\DayExercise;
use App\Models\ProgramDraft;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $description = '';
    public int $length_weeks = 0;
    public ?string $notes = null;

    // Nested structure: exercises[week][day][exercise_index] = [...]
    public array $exercises = [];

    // Track rest days: restDays[week][day] = true/false
    public array $restDays = [];

    // Track which weeks are expanded
    public array $expandedWeeks = [];

    // Draft tracking
    public bool $hasDraft = false;
    public ?string $lastSavedAt = null;

    public function mount(): void
    {
        // Load draft if it exists
        $this->loadDraft();
    }

    protected function loadDraft(): void
    {
        $draft = ProgramDraft::where('user_id', Auth::id())->first();

        if ($draft && $draft->draft_data) {
            $data = $draft->draft_data;

            $this->name = $data['name'] ?? '';
            $this->description = $data['description'] ?? '';
            $this->length_weeks = $data['length_weeks'] ?? 0;
            $this->notes = $data['notes'] ?? null;
            $this->exercises = $data['exercises'] ?? [];
            $this->restDays = $data['restDays'] ?? [];
            $this->expandedWeeks = $data['expandedWeeks'] ?? [];

            $this->hasDraft = true;
            $this->lastSavedAt = $draft->last_saved_at->diffForHumans();

            // Initialize weeks if needed
            if ($this->length_weeks > 0 && empty($this->exercises)) {
                $this->initializeWeeks();
            }
        }
    }

    public function autoSave(): void
    {
        // Only save if there's actual data
        if (empty($this->name) && $this->length_weeks === 0) {
            return;
        }

        $draftData = [
            'name' => $this->name,
            'description' => $this->description,
            'length_weeks' => $this->length_weeks,
            'notes' => $this->notes,
            'exercises' => $this->exercises,
            'restDays' => $this->restDays,
            'expandedWeeks' => $this->expandedWeeks,
        ];

        ProgramDraft::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'draft_data' => $draftData,
                'last_saved_at' => now(),
            ],
        );

        $this->hasDraft = true;
        $this->lastSavedAt = now()->diffForHumans();
    }

    public function discardDraft(): void
    {
        ProgramDraft::where('user_id', Auth::id())->delete();
        $this->hasDraft = false;
        $this->lastSavedAt = null;

        // Reset form
        $this->name = '';
        $this->description = '';
        $this->length_weeks = 0;
        $this->notes = null;
        $this->exercises = [];
        $this->restDays = [];
        $this->expandedWeeks = [];
    }

    public function updatedLengthWeeks($value): void
    {
        $newLength = (int) $value;
        $currentLength = count($this->exercises);

        if ($newLength <= 0) {
            // If set to 0 or less, clear everything
            $this->exercises = [];
            $this->restDays = [];
            $this->expandedWeeks = [];
            return;
        }

        // If increasing weeks, add empty days for new weeks only
        if ($newLength > $currentLength) {
            for ($week = $currentLength + 1; $week <= $newLength; $week++) {
                $this->exercises[$week] = [];
                $this->restDays[$week] = [];
                for ($day = 1; $day <= 7; $day++) {
                    $this->exercises[$week][$day] = [];
                    $this->restDays[$week][$day] = false;
                }
                // Expand new weeks by default
                $this->expandedWeeks[$week] = true;
            }
        } else {
            // If decreasing weeks, remove excess weeks (but preserve existing data)
            for ($week = $newLength + 1; $week <= $currentLength; $week++) {
                unset($this->exercises[$week]);
                unset($this->restDays[$week]);
                unset($this->expandedWeeks[$week]);
            }
        }

        // Ensure all weeks up to newLength have proper structure
        for ($week = 1; $week <= $newLength; $week++) {
            if (!isset($this->exercises[$week])) {
                $this->exercises[$week] = [];
            }
            for ($day = 1; $day <= 7; $day++) {
                if (!isset($this->exercises[$week][$day])) {
                    $this->exercises[$week][$day] = [];
                }
                if (!isset($this->restDays[$week][$day])) {
                    $this->restDays[$week][$day] = false;
                }
            }
            if (!isset($this->expandedWeeks[$week])) {
                $this->expandedWeeks[$week] = true;
            }
        }

        $this->scheduleAutoSave();
    }

    public function updatedName(): void
    {
        $this->scheduleAutoSave();
    }

    public function updatedDescription(): void
    {
        $this->scheduleAutoSave();
    }

    public function updatedNotes(): void
    {
        $this->scheduleAutoSave();
    }

    public function updatedExercises(): void
    {
        $this->scheduleAutoSave();
    }

    public function updatedRestDays(): void
    {
        $this->scheduleAutoSave();
    }

    protected function scheduleAutoSave(): void
    {
        // Dispatch event for JavaScript to handle debouncing
        $this->dispatch('schedule-autosave');
    }

    public function toggleWeek($week): void
    {
        $week = (int) $week;
        $this->expandedWeeks[$week] = !($this->expandedWeeks[$week] ?? false);
    }

    protected function initializeWeeks(): void
    {
        // Only initialize if exercises array is empty
        if (empty($this->exercises)) {
            $this->exercises = [];
            $this->restDays = [];
            for ($week = 1; $week <= $this->length_weeks; $week++) {
                $this->exercises[$week] = [];
                $this->restDays[$week] = [];
                for ($day = 1; $day <= 7; $day++) {
                    $this->exercises[$week][$day] = [];
                    $this->restDays[$week][$day] = false;
                }
                // Expand all weeks by default
                $this->expandedWeeks[$week] = true;
            }
        } else {
            // If exercises exist, use updatedLengthWeeks logic to adjust
            $this->updatedLengthWeeks($this->length_weeks);
        }
    }

    public function addExercise($week, $day): void
    {
        $week = (int) $week;
        $day = (int) $day;

        if (!isset($this->exercises[$week][$day])) {
            $this->exercises[$week][$day] = [];
        }

        $this->exercises[$week][$day][] = [
            'name' => '',
            'type' => 'strength',
            'sets' => null,
            'sets_min' => null,
            'sets_max' => null,
            'reps' => null,
            'reps_min' => null,
            'reps_max' => null,
            'weight' => null,
            'weight_min' => null,
            'weight_max' => null,
            'distance' => null,
            'distance_min' => null,
            'distance_max' => null,
            'time_seconds' => null,
            'time_seconds_min' => null,
            'time_seconds_max' => null,
        ];
    }

    public function removeExercise($week, $day, $index): void
    {
        $week = (int) $week;
        $day = (int) $day;
        $index = (int) $index;

        if (isset($this->exercises[$week][$day][$index])) {
            unset($this->exercises[$week][$day][$index]);
            $this->exercises[$week][$day] = array_values($this->exercises[$week][$day]);
        }
    }

    public function copyDay($week, $sourceDay, $targetDay): void
    {
        $week = (int) $week;
        $sourceDay = (int) $sourceDay;
        $targetDay = (int) $targetDay;

        if (!isset($this->exercises[$week][$sourceDay])) {
            return;
        }

        // Deep copy the exercises array
        $this->exercises[$week][$targetDay] = [];
        foreach ($this->exercises[$week][$sourceDay] as $exercise) {
            $this->exercises[$week][$targetDay][] = [
                'name' => $exercise['name'] ?? '',
                'type' => $exercise['type'] ?? 'strength',
                'sets' => $exercise['sets'] ?? null,
                'sets_min' => $exercise['sets_min'] ?? null,
                'sets_max' => $exercise['sets_max'] ?? null,
                'reps' => $exercise['reps'] ?? null,
                'reps_min' => $exercise['reps_min'] ?? null,
                'reps_max' => $exercise['reps_max'] ?? null,
                'weight' => $exercise['weight'] ?? null,
                'weight_min' => $exercise['weight_min'] ?? null,
                'weight_max' => $exercise['weight_max'] ?? null,
                'distance' => $exercise['distance'] ?? null,
                'distance_min' => $exercise['distance_min'] ?? null,
                'distance_max' => $exercise['distance_max'] ?? null,
                'time_seconds' => $exercise['time_seconds'] ?? null,
                'time_seconds_min' => $exercise['time_seconds_min'] ?? null,
                'time_seconds_max' => $exercise['time_seconds_max'] ?? null,
            ];
        }

        // Also copy the rest day status
        if (isset($this->restDays[$week][$sourceDay])) {
            $this->restDays[$week][$targetDay] = $this->restDays[$week][$sourceDay];
        }

        session()->flash(
            'copied',
            __('Day :source copied to Day :target successfully!', [
                'source' => $sourceDay,
                'target' => $targetDay,
            ]),
        );
    }

    public function getAvailableDaysToCopy($week, $currentDay): array
    {
        $available = [];
        for ($day = 1; $day <= 7; $day++) {
            if ($day != $currentDay && !empty($this->exercises[$week][$day])) {
                $exerciseCount = collect($this->exercises[$week][$day])
                    ->filter(fn($ex) => !empty($ex['name']))
                    ->count();

                if ($exerciseCount > 0) {
                    $available[$day] = $exerciseCount;
                }
            }
        }
        return $available;
    }

    public function copyWeek($sourceWeek, $targetWeek): void
    {
        $sourceWeek = (int) $sourceWeek;
        $targetWeek = (int) $targetWeek;

        if (!isset($this->exercises[$sourceWeek])) {
            return;
        }

        // Copy all days and exercises from source week to target week
        $this->exercises[$targetWeek] = [];
        $this->restDays[$targetWeek] = [];

        foreach ($this->exercises[$sourceWeek] as $dayNum => $dayExercises) {
            // Deep copy the exercises array for each day
            $this->exercises[$targetWeek][$dayNum] = [];
            foreach ($dayExercises as $exercise) {
                $this->exercises[$targetWeek][$dayNum][] = [
                    'name' => $exercise['name'] ?? '',
                    'type' => $exercise['type'] ?? 'strength',
                    'sets' => $exercise['sets'] ?? null,
                    'sets_min' => $exercise['sets_min'] ?? null,
                    'sets_max' => $exercise['sets_max'] ?? null,
                    'reps' => $exercise['reps'] ?? null,
                    'reps_min' => $exercise['reps_min'] ?? null,
                    'reps_max' => $exercise['reps_max'] ?? null,
                    'weight' => $exercise['weight'] ?? null,
                    'weight_min' => $exercise['weight_min'] ?? null,
                    'weight_max' => $exercise['weight_max'] ?? null,
                    'distance' => $exercise['distance'] ?? null,
                    'distance_min' => $exercise['distance_min'] ?? null,
                    'distance_max' => $exercise['distance_max'] ?? null,
                    'time_seconds' => $exercise['time_seconds'] ?? null,
                    'time_seconds_min' => $exercise['time_seconds_min'] ?? null,
                    'time_seconds_max' => $exercise['time_seconds_max'] ?? null,
                ];
            }

            // Copy the rest day status
            if (isset($this->restDays[$sourceWeek][$dayNum])) {
                $this->restDays[$targetWeek][$dayNum] = $this->restDays[$sourceWeek][$dayNum];
            }
        }

        session()->flash(
            'copied',
            __('Week :source copied to Week :target successfully!', [
                'source' => $sourceWeek,
                'target' => $targetWeek,
            ]),
        );
    }

    public function getAvailableWeeksToCopy($currentWeek): array
    {
        $available = [];
        for ($week = 1; $week <= $this->length_weeks; $week++) {
            if ($week != $currentWeek && !empty($this->exercises[$week])) {
                // Count total exercises across all days in the week
                $totalExercises = 0;
                foreach ($this->exercises[$week] as $dayExercises) {
                    $totalExercises += collect($dayExercises)->filter(fn($ex) => !empty($ex['name']))->count();
                }

                if ($totalExercises > 0) {
                    $available[$week] = $totalExercises;
                }
            }
        }
        return $available;
    }

    public function save(): void
    {
        $user = Auth::user();

        // Check if user can create another program
        if (!$user->canCreateProgram()) {
            $max = $user->getMaxPrograms();
            $this->addError('limit', __("You've reached your program limit of :max programs. Upgrade your subscription to create more programs.", ['max' => $max]));
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'length_weeks' => ['required', 'integer', 'min:1', 'max:52'],
            'notes' => ['nullable', 'string'],
            'exercises' => ['required', 'array'],
        ]);

        // Validate only exercises that have a name (non-empty exercises)
        foreach ($this->exercises as $weekNum => $days) {
            foreach ($days as $dayNum => $dayExercises) {
                foreach ($dayExercises as $index => $exercise) {
                    if (!empty($exercise['name'])) {
                        $this->validate([
                            "exercises.{$weekNum}.{$dayNum}.{$index}.name" => ['required', 'string', 'max:255'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.type" => ['required', 'string', 'max:255'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.sets" => ['nullable', 'integer', 'min:1'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.sets_min" => ['nullable', 'integer', 'min:1'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.sets_max" => ['nullable', 'integer', 'min:1'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.reps" => ['nullable', 'integer', 'min:1'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.reps_min" => ['nullable', 'integer', 'min:1'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.reps_max" => ['nullable', 'integer', 'min:1'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.weight" => ['nullable', 'numeric', 'min:0'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.weight_min" => ['nullable', 'numeric', 'min:0'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.weight_max" => ['nullable', 'numeric', 'min:0'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.distance" => ['nullable', 'numeric', 'min:0'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.distance_min" => ['nullable', 'numeric', 'min:0'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.distance_max" => ['nullable', 'numeric', 'min:0'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.time_seconds" => ['nullable', 'integer', 'min:0'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.time_seconds_min" => ['nullable', 'integer', 'min:0'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.time_seconds_max" => ['nullable', 'integer', 'min:0'],
                        ]);

                        // Validate that max >= min for ranges
                        $exercise = $this->exercises[$weekNum][$dayNum][$index];
                        if (isset($exercise['sets_min']) && isset($exercise['sets_max']) && $exercise['sets_max'] < $exercise['sets_min']) {
                            $this->addError("exercises.{$weekNum}.{$dayNum}.{$index}.sets_max", 'Sets max must be greater than or equal to sets min.');
                        }
                        if (isset($exercise['reps_min']) && isset($exercise['reps_max']) && $exercise['reps_max'] < $exercise['reps_min']) {
                            $this->addError("exercises.{$weekNum}.{$dayNum}.{$index}.reps_max", 'Reps max must be greater than or equal to reps min.');
                        }
                        if (isset($exercise['weight_min']) && isset($exercise['weight_max']) && $exercise['weight_max'] < $exercise['weight_min']) {
                            $this->addError("exercises.{$weekNum}.{$dayNum}.{$index}.weight_max", 'Weight max must be greater than or equal to weight min.');
                        }
                        if (isset($exercise['distance_min']) && isset($exercise['distance_max']) && $exercise['distance_max'] < $exercise['distance_min']) {
                            $this->addError("exercises.{$weekNum}.{$dayNum}.{$index}.distance_max", 'Distance max must be greater than or equal to distance min.');
                        }
                        if (isset($exercise['time_seconds_min']) && isset($exercise['time_seconds_max']) && $exercise['time_seconds_max'] < $exercise['time_seconds_min']) {
                            $this->addError("exercises.{$weekNum}.{$dayNum}.{$index}.time_seconds_max", 'Time max must be greater than or equal to time min.');
                        }
                    }
                }
            }
        }

        // Increase execution time limit for large programs
        set_time_limit(300); // 5 minutes

        try {
            DB::transaction(function () use ($validated, $user) {
                // Create program as template (no dates, status = template)
                $program = Program::create([
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'length_weeks' => $validated['length_weeks'],
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'template',
                    'user_id' => $user->id,
                    'trainer_id' => $user->isTrainer() ? $user->id : null,
                ]);

                // Bulk insert weeks
                $weeksToInsert = [];
                for ($weekNum = 1; $weekNum <= $validated['length_weeks']; $weekNum++) {
                    $weeksToInsert[] = [
                        'program_id' => $program->id,
                        'week_number' => $weekNum,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($weeksToInsert)) {
                    DB::table('program_weeks')->insert($weeksToInsert);
                }

                // Get inserted weeks - create both keyed collections for efficient lookup
                $insertedWeeksCollection = DB::table('program_weeks')->where('program_id', $program->id)->orderBy('week_number')->get();

                $weeksById = $insertedWeeksCollection->keyBy('id');
                $insertedWeeks = $insertedWeeksCollection->keyBy('week_number');

                // Bulk insert days
                $daysToInsert = [];
                foreach ($this->exercises as $weekNum => $days) {
                    $programWeek = $insertedWeeks[$weekNum];

                    foreach ($days as $dayNum => $dayExercises) {
                        // Check if day has any exercises with names
                        $hasExercises = false;
                        foreach ($dayExercises as $exercise) {
                            if (!empty($exercise['name'])) {
                                $hasExercises = true;
                                break;
                            }
                        }

                        $isRestDay = $this->restDays[$weekNum][$dayNum] ?? false;
                        if (!$hasExercises) {
                            $isRestDay = true;
                        }

                        $daysToInsert[] = [
                            'program_week_id' => $programWeek->id,
                            'day_number' => $dayNum,
                            'label' => "Day $dayNum",
                            'is_rest_day' => $isRestDay,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (!empty($daysToInsert)) {
                    DB::table('program_days')->insert($daysToInsert);
                }

                // Get inserted days
                $insertedDays = DB::table('program_days')->whereIn('program_week_id', $insertedWeeks->pluck('id'))->orderBy('program_week_id')->orderBy('day_number')->get();

                // Create day map: [week_number][day_number] => day_id
                $dayMap = [];
                foreach ($insertedDays as $day) {
                    // Use weeksById for efficient lookup by id, with null check for defensive programming
                    $week = $weeksById->get($day->program_week_id);
                    if (!$week) {
                        // Log error and skip this day if week not found (shouldn't happen in normal operation)
                        \Log::warning('Program creation: Day references non-existent week', [
                            'day_id' => $day->id,
                            'program_week_id' => $day->program_week_id,
                            'program_id' => $program->id,
                        ]);
                        continue;
                    }
                    $weekNum = $week->week_number;
                    $dayMap[$weekNum][$day->day_number] = $day->id;
                }

                // Bulk insert exercises in chunks
                $exercisesToInsert = [];
                foreach ($this->exercises as $weekNum => $days) {
                    foreach ($days as $dayNum => $dayExercises) {
                        $programDayId = $dayMap[$weekNum][$dayNum] ?? null;
                        if (!$programDayId) {
                            continue;
                        }

                        foreach ($dayExercises as $order => $exercise) {
                            if (!empty($exercise['name'])) {
                                $exercisesToInsert[] = [
                                    'program_day_id' => $programDayId,
                                    'name' => $exercise['name'],
                                    'type' => $exercise['type'] ?? 'strength',
                                    'sets' => $exercise['sets'] ?? null,
                                    'sets_min' => $exercise['sets_min'] ?? null,
                                    'sets_max' => $exercise['sets_max'] ?? null,
                                    'reps' => $exercise['reps'] ?? null,
                                    'reps_min' => $exercise['reps_min'] ?? null,
                                    'reps_max' => $exercise['reps_max'] ?? null,
                                    'weight' => $exercise['weight'] ?? null,
                                    'weight_min' => $exercise['weight_min'] ?? null,
                                    'weight_max' => $exercise['weight_max'] ?? null,
                                    'distance' => $exercise['distance'] ?? null,
                                    'distance_min' => $exercise['distance_min'] ?? null,
                                    'distance_max' => $exercise['distance_max'] ?? null,
                                    'time_seconds' => $exercise['time_seconds'] ?? null,
                                    'time_seconds_min' => $exercise['time_seconds_min'] ?? null,
                                    'time_seconds_max' => $exercise['time_seconds_max'] ?? null,
                                    'order' => $order + 1,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }
                }

                // Insert exercises in chunks of 500 to avoid query size limits
                if (!empty($exercisesToInsert)) {
                    foreach (array_chunk($exercisesToInsert, 500) as $chunk) {
                        DB::table('day_exercises')->insert($chunk);
                    }
                }
            });

            // Delete draft after successful creation
            ProgramDraft::where('user_id', Auth::id())->delete();
            $this->hasDraft = false;

            session()->flash('success', __('Program created successfully!'));
            $this->redirect(route('programs.show', Program::where('user_id', Auth::id())->latest()->first()));
        } catch (\Exception $e) {
            \Log::error('Program creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->addError('general', __('An error occurred while creating the program. Your draft has been saved. Please try again.'));
        }
    }
}; ?>

<section class="w-full">
    @if (session('success'))
        <div
            class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/50 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div
            class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/50 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div
            class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/50 dark:text-red-200">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($hasDraft)
        <div
            class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-900/50 dark:text-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <strong>{{ __('Draft saved') }}</strong>
                    <span class="ml-2">{{ __('Last saved :time', ['time' => $lastSavedAt]) }}</span>
                </div>
                <button wire:click="discardDraft" type="button"
                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 underline">
                    {{ __('Discard draft') }}
                </button>
            </div>
        </div>
    @endif

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
            {{ __('Create Program') }}
        </h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Create a complete training program with all weeks, days, and exercises') }}
        </p>
    </div>

    <form wire:submit="save" class="space-y-8">
        <!-- Program Details -->
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Program Details') }}
            </h2>

            <flux:input wire:model="name" :label="__('Program Name')" type="text" required autofocus
                placeholder="e.g., 12 Week Strength Program" />

            <flux:textarea wire:model="description" :label="__('Description')" placeholder="Describe your program..."
                rows="3" />

            <flux:input wire:model.live="length_weeks" :label="__('Length (weeks)')" type="number" required
                min="1" max="52" placeholder="Select number of weeks" />

            <flux:textarea wire:model="notes" :label="__('Notes')" placeholder="Additional notes..." rows="2" />
        </div>

        <!-- Weeks, Days, and Exercises -->
        @if ($length_weeks > 0)
            @if (session('copied'))
                <div
                    class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-900/50 dark:text-blue-200">
                    {{ session('copied') }}
                </div>
            @endif

            <div class="space-y-4">
                @foreach ($exercises as $weekNum => $days)
                    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                        <div
                            class="flex items-center justify-between p-6 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ __('Week :number', ['number' => $weekNum]) }}
                            </h3>
                            <div class="flex items-center gap-3">
                                @php
                                    $availableWeeks = $this->getAvailableWeeksToCopy($weekNum);
                                @endphp
                                @if (!empty($availableWeeks))
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button type="button" variant="ghost" size="sm">
                                            {{ __('Copy Week') }}
                                        </flux:button>

                                        <flux:menu>
                                            <flux:menu.heading>{{ __('Copy from:') }}
                                            </flux:menu.heading>
                                            @foreach ($availableWeeks as $sourceWeek => $exerciseCount)
                                                <flux:menu.item
                                                    wire:click="copyWeek({{ $sourceWeek }}, {{ $weekNum }})"
                                                    type="button">
                                                    {{ __('Week :number (:count :exercise)', [
                                                        'number' => $sourceWeek,
                                                        'count' => $exerciseCount,
                                                        'exercise' => $exerciseCount === 1 ? 'exercise' : 'exercises',
                                                    ]) }}
                                                </flux:menu.item>
                                            @endforeach
                                        </flux:menu>
                                    </flux:dropdown>
                                @endif
                                <button type="button" wire:click="toggleWeek({{ $weekNum }})"
                                    class="p-1 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded transition-colors">
                                    <svg class="w-5 h-5 text-zinc-500 dark:text-zinc-400 transition-transform {{ $expandedWeeks[$weekNum] ?? true ? 'rotate-180' : '' }}"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        @if ($expandedWeeks[$weekNum] ?? true)
                            <div class="px-6 pb-6">
                                <div class="space-y-4">
                                    @foreach ($days as $dayNum => $dayExercises)
                                        <div
                                            class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-4 bg-neutral-50 dark:bg-neutral-800/50">
                                            <div
                                                class="mb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                                <div
                                                    class="flex flex-col items-center sm:items-start text-center sm:text-left">
                                                    <h4 class="font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ __('Day :number', ['number' => $dayNum]) }}
                                                    </h4>
                                                    @php
                                                        $hasExercises =
                                                            !empty($dayExercises) &&
                                                            collect($dayExercises)->contains(
                                                                fn($ex) => !empty($ex['name']),
                                                            );
                                                    @endphp
                                                    @if (!$hasExercises)
                                                        <span
                                                            class="text-xs text-zinc-500 dark:text-zinc-400 italic mt-1">
                                                            {{ __('(Rest Day - No exercises)') }}
                                                        </span>
                                                    @else
                                                        <label
                                                            class="flex items-center justify-center sm:justify-start gap-2 text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                                            <input type="checkbox"
                                                                wire:model="restDays.{{ $weekNum }}.{{ $dayNum }}"
                                                                class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                                            <span>{{ __('Rest Day') }}</span>
                                                        </label>
                                                    @endif
                                                </div>
                                                <div
                                                    class="flex items-center justify-center sm:justify-end gap-2 flex-wrap">
                                                    <flux:button type="button"
                                                        wire:click="addExercise({{ $weekNum }}, {{ $dayNum }})"
                                                        variant="ghost" size="sm">
                                                        {{ __('+ Add Exercise') }}
                                                    </flux:button>

                                                    @php
                                                        $availableDays = $this->getAvailableDaysToCopy(
                                                            $weekNum,
                                                            $dayNum,
                                                        );
                                                    @endphp

                                                    @if (!empty($availableDays))
                                                        <flux:dropdown position="bottom" align="end">
                                                            <flux:button type="button" variant="ghost" size="sm">
                                                                {{ __('Copy Day') }}
                                                            </flux:button>

                                                            <flux:menu>
                                                                <flux:menu.heading>{{ __('Copy from:') }}
                                                                </flux:menu.heading>
                                                                @foreach ($availableDays as $sourceDay => $exerciseCount)
                                                                    <flux:menu.item
                                                                        wire:click="copyDay({{ $weekNum }}, {{ $sourceDay }}, {{ $dayNum }})"
                                                                        type="button">
                                                                        {{ __('Day :number (:count :exercise)', [
                                                                            'number' => $sourceDay,
                                                                            'count' => $exerciseCount,
                                                                            'exercise' => $exerciseCount === 1 ? 'exercise' : 'exercises',
                                                                        ]) }}
                                                                    </flux:menu.item>
                                                                @endforeach
                                                            </flux:menu>
                                                        </flux:dropdown>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="space-y-3">
                                                @foreach ($dayExercises as $index => $exercise)
                                                    <div wire:key="exercise-{{ $weekNum }}-{{ $dayNum }}-{{ $index }}"
                                                        class="rounded border border-neutral-200 dark:border-neutral-600 p-4 bg-white dark:bg-neutral-900">
                                                        <div class="mb-3 flex items-center justify-between">
                                                            <span
                                                                class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                                                {{ __('Exercise :number', ['number' => $index + 1]) }}
                                                            </span>
                                                            <flux:button type="button"
                                                                wire:click="removeExercise({{ $weekNum }}, {{ $dayNum }}, {{ $index }})"
                                                                variant="ghost" size="sm"
                                                                class="text-red-600 hover:text-red-700">
                                                                {{ __('Remove') }}
                                                            </flux:button>
                                                        </div>

                                                        <div class="grid gap-3 md:grid-cols-2">
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                                    {{ __('Exercise Name') }}
                                                                </label>
                                                                <input type="text"
                                                                    wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.name"
                                                                    required placeholder="e.g., Bench Press"
                                                                    class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                            </div>

                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                                    {{ __('Type') }}
                                                                </label>
                                                                <select
                                                                    wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.type"
                                                                    required
                                                                    class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                                                    <option value="strength">Strength</option>
                                                                    <option value="cardio">Cardio</option>
                                                                    <option value="flexibility">Flexibility</option>
                                                                    <option value="other">Other</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="mt-3 grid gap-3 md:grid-cols-2">
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                                    {{ __('Sets') }}
                                                                </label>
                                                                <div class="flex items-center gap-2">
                                                                    <input type="number"
                                                                        wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.sets_min"
                                                                        min="1" placeholder="Min"
                                                                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                                    <span
                                                                        class="text-zinc-500 dark:text-zinc-400">-</span>
                                                                    <input type="number"
                                                                        wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.sets_max"
                                                                        min="1" placeholder="Max"
                                                                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                                    {{ __('Reps') }}
                                                                </label>
                                                                <div class="flex items-center gap-2">
                                                                    <input type="number"
                                                                        wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.reps_min"
                                                                        min="1" placeholder="Min"
                                                                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                                    <span
                                                                        class="text-zinc-500 dark:text-zinc-400">-</span>
                                                                    <input type="number"
                                                                        wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.reps_max"
                                                                        min="1" placeholder="Max"
                                                                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                                    {{ __('Weight (lbs)') }}
                                                                </label>
                                                                <div class="flex items-center gap-2">
                                                                    <input type="number"
                                                                        wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.weight_min"
                                                                        step="0.01" min="0"
                                                                        placeholder="Min"
                                                                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                                    <span
                                                                        class="text-zinc-500 dark:text-zinc-400">-</span>
                                                                    <input type="number"
                                                                        wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.weight_max"
                                                                        step="0.01" min="0"
                                                                        placeholder="Max"
                                                                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                                    {{ __('Distance (miles)') }}
                                                                </label>
                                                                <div class="flex items-center gap-2">
                                                                    <input type="number"
                                                                        wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.distance_min"
                                                                        step="0.01" min="0"
                                                                        placeholder="Min"
                                                                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                                    <span
                                                                        class="text-zinc-500 dark:text-zinc-400">-</span>
                                                                    <input type="number"
                                                                        wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.distance_max"
                                                                        step="0.01" min="0"
                                                                        placeholder="Max"
                                                                        class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mt-3">
                                                            <label
                                                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                                {{ __('Time (seconds)') }}
                                                            </label>
                                                            <div class="flex items-center gap-2">
                                                                <input type="number"
                                                                    wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.time_seconds_min"
                                                                    min="0" placeholder="Min"
                                                                    class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                                <span class="text-zinc-500 dark:text-zinc-400">-</span>
                                                                <input type="number"
                                                                    wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.time_seconds_max"
                                                                    min="0" placeholder="Max"
                                                                    class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach

                                                @if (empty($dayExercises))
                                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 italic">
                                                        {{ __('No exercises added yet. Click "Add Exercise" to add multiple exercises to this day.') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-12 text-center">
                <p class="text-zinc-600 dark:text-zinc-400">
                    {{ __('Please select the program length (weeks) above to start adding exercises.') }}
                </p>
            </div>
        @endif

        <!-- Submit Button -->
        <div class="flex items-center gap-4 border-t border-neutral-200 dark:border-neutral-700 pt-6">
            <flux:button type="submit" variant="primary" class="w-full md:w-auto">
                {{ __('Create Complete Program') }}
            </flux:button>

            <flux:button href="{{ route('programs.index') }}" variant="ghost" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</section>

@script
    <script>
        let autoSaveTimeout;

        // Debounced auto-save (2 seconds after last change)
        $wire.on('schedule-autosave', () => {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                $wire.call('autoSave');
            }, 2000); // 2 seconds debounce
        });

        // Also auto-save every 60 seconds as backup
        setInterval(() => {
            $wire.call('autoSave');
        }, 60000);
    </script>
@endscript
