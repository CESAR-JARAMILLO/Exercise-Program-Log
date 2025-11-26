<?php

use App\Models\Program;
use App\Models\ProgramWeek;
use App\Models\ProgramDay;
use App\Models\DayExercise;
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

    public function updatedLengthWeeks($value): void
    {
        // Reinitialize when weeks change
        $this->initializeWeeks();
    }

    public function toggleWeek($week): void
    {
        $week = (int) $week;
        $this->expandedWeeks[$week] = !($this->expandedWeeks[$week] ?? false);
    }

    protected function initializeWeeks(): void
    {
        $this->exercises = [];
        $this->restDays = [];
        for ($week = 1; $week <= $this->length_weeks; $week++) {
            for ($day = 1; $day <= 7; $day++) {
                $this->exercises[$week][$day] = [];
                $this->restDays[$week][$day] = false;
            }
            // Expand all weeks by default
            $this->expandedWeeks[$week] = true;
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
            'reps' => null,
            'weight' => null,
            'distance' => null,
            'time_seconds' => null,
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
                            "exercises.{$weekNum}.{$dayNum}.{$index}.reps" => ['nullable', 'integer', 'min:1'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.weight" => ['nullable', 'numeric', 'min:0'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.distance" => ['nullable', 'numeric', 'min:0'],
                            "exercises.{$weekNum}.{$dayNum}.{$index}.time_seconds" => ['nullable', 'integer', 'min:0'],
                        ]);
                    }
                }
            }
        }

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

            // Create weeks, days, and exercises
            foreach ($this->exercises as $weekNum => $days) {
                $programWeek = ProgramWeek::create([
                    'program_id' => $program->id,
                    'week_number' => $weekNum,
                ]);

                foreach ($days as $dayNum => $dayExercises) {
                    // Check if day has any exercises with names
                    $hasExercises = false;
                    foreach ($dayExercises as $exercise) {
                        if (!empty($exercise['name'])) {
                            $hasExercises = true;
                            break;
                        }
                    }

                    // If no exercises, automatically make it a rest day
                    $isRestDay = $this->restDays[$weekNum][$dayNum] ?? false;
                    if (!$hasExercises) {
                        $isRestDay = true;
                    }

                    $programDay = ProgramDay::create([
                        'program_week_id' => $programWeek->id,
                        'day_number' => $dayNum,
                        'label' => "Day $dayNum",
                        'is_rest_day' => $isRestDay,
                    ]);

                    // Add exercises for this day (only save exercises with names)
                    foreach ($dayExercises as $order => $exercise) {
                        if (!empty($exercise['name'])) {
                            DayExercise::create([
                                'program_day_id' => $programDay->id,
                                'name' => $exercise['name'],
                                'type' => $exercise['type'] ?? 'strength',
                                'sets' => $exercise['sets'] ?? null,
                                'reps' => $exercise['reps'] ?? null,
                                'weight' => $exercise['weight'] ?? null,
                                'distance' => $exercise['distance'] ?? null,
                                'time_seconds' => $exercise['time_seconds'] ?? null,
                                'order' => $order + 1,
                            ]);
                        }
                    }
                }
            }
        });

        session()->flash('success', __('Program created successfully!'));
        $this->redirect(route('programs.show', Program::where('user_id', Auth::id())->latest()->first()));
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
            <div class="space-y-4">
                @foreach ($exercises as $weekNum => $days)
                    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                        <button type="button" wire:click="toggleWeek({{ $weekNum }})"
                            class="w-full flex items-center justify-between p-6 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ __('Week :number', ['number' => $weekNum]) }}
                            </h3>
                            <svg class="w-5 h-5 text-zinc-500 dark:text-zinc-400 transition-transform {{ $expandedWeeks[$weekNum] ?? true ? 'rotate-180' : '' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        @if ($expandedWeeks[$weekNum] ?? true)
                            <div class="px-6 pb-6">
                                <div class="space-y-4">
                                    @foreach ($days as $dayNum => $dayExercises)
                                        <div
                                            class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-4 bg-neutral-50 dark:bg-neutral-800/50">
                                            <div class="mb-3 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
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
                                                        <span class="text-xs text-zinc-500 dark:text-zinc-400 italic">
                                                            {{ __('(Rest Day - No exercises)') }}
                                                        </span>
                                                    @else
                                                        <label
                                                            class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                                            <input type="checkbox"
                                                                wire:model="restDays.{{ $weekNum }}.{{ $dayNum }}"
                                                                class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                                            <span>{{ __('Rest Day') }}</span>
                                                        </label>
                                                    @endif
                                                </div>
                                                <flux:button type="button"
                                                    wire:click="addExercise({{ $weekNum }}, {{ $dayNum }})"
                                                    variant="ghost" size="sm">
                                                    {{ __('+ Add Exercise') }}
                                                </flux:button>
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

                                                        <div class="mt-3 grid gap-3 md:grid-cols-4">
                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                                    {{ __('Sets') }}
                                                                </label>
                                                                <input type="number"
                                                                    wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.sets"
                                                                    min="1" placeholder="3"
                                                                    class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                            </div>

                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                                    {{ __('Reps') }}
                                                                </label>
                                                                <input type="number"
                                                                    wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.reps"
                                                                    min="1" placeholder="10"
                                                                    class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                            </div>

                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                                    {{ __('Weight (lbs)') }}
                                                                </label>
                                                                <input type="number"
                                                                    wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.weight"
                                                                    step="0.01" min="0" placeholder="135"
                                                                    class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                            </div>

                                                            <div>
                                                                <label
                                                                    class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                                    {{ __('Distance (miles)') }}
                                                                </label>
                                                                <input type="number"
                                                                    wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.distance"
                                                                    step="0.01" min="0" placeholder="3.5"
                                                                    class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
                                                            </div>
                                                        </div>

                                                        <div class="mt-3">
                                                            <label
                                                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                                                {{ __('Time (seconds)') }}
                                                            </label>
                                                            <input type="number"
                                                                wire:model.live="exercises.{{ $weekNum }}.{{ $dayNum }}.{{ $index }}.time_seconds"
                                                                min="0" placeholder="1800"
                                                                class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
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
