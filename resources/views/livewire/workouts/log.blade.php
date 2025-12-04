<?php

use App\Models\ActiveProgram;
use App\Models\WorkoutLog;
use App\Models\WorkoutExercise;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component {
    public int $activeProgramId;
    public string $workoutDate;
    public ?int $workoutLogId = null;
    public ?string $notes = null;
    
    // Exercise data: [exercise_id => [sets_actual, reps_actual, etc.]]
    public array $exercises = [];

    public function mount($activeProgram, $date): void
    {
        // Handle route parameters
        if ($activeProgram instanceof ActiveProgram) {
            $activeProgram = $activeProgram;
        } else {
            $activeProgram = ActiveProgram::findOrFail($activeProgram);
        }
        
        abort_unless($activeProgram->user_id === Auth::id(), 403);
        
        $this->activeProgramId = $activeProgram->id;
        $this->workoutDate = Carbon::parse($date)->format('Y-m-d');
        
        // Check if workout is already logged
        $workoutLog = $activeProgram->getWorkoutLogForDate($this->workoutDate);
        
        if ($workoutLog) {
            $this->workoutLogId = $workoutLog->id;
            $this->notes = $workoutLog->notes;
            
            // Load existing exercise data
            foreach ($workoutLog->exercises as $workoutExercise) {
                $timeSeconds = $workoutExercise->time_seconds_actual;
                $this->exercises[$workoutExercise->day_exercise_id] = [
                    'name' => $workoutExercise->name,
                    'type' => $workoutExercise->type,
                    'sets_actual' => $workoutExercise->sets_actual,
                    'reps_actual' => $workoutExercise->reps_actual,
                    'weight_actual' => $workoutExercise->weight_actual,
                    'distance_actual' => $workoutExercise->distance_actual,
                    'time_seconds_actual' => $timeSeconds,
                    'time_hours' => $timeSeconds ? floor($timeSeconds / 3600) : null,
                    'time_minutes' => $timeSeconds ? floor(($timeSeconds % 3600) / 60) : null,
                    'time_seconds' => $timeSeconds ? ($timeSeconds % 60) : null,
                    'notes' => $workoutExercise->notes,
                ];
            }
        }
    }

    public function with(): array
    {
        $activeProgram = ActiveProgram::with(['program.weeks.days.exercises'])->findOrFail($this->activeProgramId);
        abort_unless($activeProgram->user_id === Auth::id(), 403);
        
        // Find which day this date corresponds to
        $scheduled = $activeProgram->hasScheduledWorkout(Carbon::parse($this->workoutDate));
        
        if (!$scheduled) {
            abort(404, 'No workout scheduled for this date.');
        }
        
        $programDay = $activeProgram->program->weeks
            ->where('week_number', $scheduled['week'])
            ->first()
            ->days
            ->where('day_number', $scheduled['day'])
            ->first();
        
        $targetExercises = $programDay->exercises->sortBy('order');
        
        // Initialize exercises array with target data if not already set
        foreach ($targetExercises as $targetExercise) {
            if (!isset($this->exercises[$targetExercise->id])) {
                $this->exercises[$targetExercise->id] = [
                    'name' => $targetExercise->name,
                    'type' => $targetExercise->type,
                    'sets_actual' => null,
                    'reps_actual' => null,
                    'weight_actual' => null,
                    'distance_actual' => null,
                    'time_seconds_actual' => null,
                    'time_hours' => null,
                    'time_minutes' => null,
                    'time_seconds' => null,
                    'notes' => null,
                ];
            } else {
                // Ensure time fields are initialized even if time_seconds_actual exists
                if (!isset($this->exercises[$targetExercise->id]['time_hours'])) {
                    $timeSeconds = $this->exercises[$targetExercise->id]['time_seconds_actual'] ?? null;
                    $this->exercises[$targetExercise->id]['time_hours'] = $timeSeconds ? floor($timeSeconds / 3600) : null;
                    $this->exercises[$targetExercise->id]['time_minutes'] = $timeSeconds ? floor(($timeSeconds % 3600) / 60) : null;
                    $this->exercises[$targetExercise->id]['time_seconds'] = $timeSeconds ? ($timeSeconds % 60) : null;
                }
            }
        }
        
        $workoutLog = $this->workoutLogId ? WorkoutLog::find($this->workoutLogId) : null;
        
        return [
            'activeProgram' => $activeProgram,
            'programDay' => $programDay,
            'targetExercises' => $targetExercises,
            'scheduled' => $scheduled,
            'workoutLog' => $workoutLog,
        ];
    }

    public function save(): void
    {
        $activeProgram = ActiveProgram::findOrFail($this->activeProgramId);
        abort_unless($activeProgram->user_id === Auth::id(), 403);
        
        $scheduled = $activeProgram->hasScheduledWorkout(Carbon::parse($this->workoutDate));
        if (!$scheduled) {
            session()->flash('error', __('No workout scheduled for this date.'));
            return;
        }
        
        $validated = $this->validate([
            'notes' => ['nullable', 'string'],
            'exercises' => ['required', 'array'],
        ]);
        
        DB::transaction(function () use ($activeProgram, $scheduled, $validated) {
            // Create or update workout log
            if ($this->workoutLogId) {
                $workoutLog = WorkoutLog::findOrFail($this->workoutLogId);
                $workoutLog->update([
                    'notes' => $validated['notes'],
                ]);
                
                // Delete existing exercises
                $workoutLog->exercises()->delete();
            } else {
                $workoutLog = WorkoutLog::create([
                    'user_id' => Auth::id(),
                    'active_program_id' => $activeProgram->id,
                    'program_day_id' => $scheduled['program_day_id'],
                    'workout_date' => $this->workoutDate,
                    'notes' => $validated['notes'],
                ]);
            }
            
            // Create workout exercises
            $order = 1;
            foreach ($this->exercises as $dayExerciseId => $exerciseData) {
                if (!empty($exerciseData['name'])) {
                    // Convert time from hours/minutes/seconds to total seconds
                    $timeSeconds = null;
                    if (isset($exerciseData['time_hours']) || isset($exerciseData['time_minutes']) || isset($exerciseData['time_seconds'])) {
                        $hours = (int)($exerciseData['time_hours'] ?? 0);
                        $minutes = (int)($exerciseData['time_minutes'] ?? 0);
                        $seconds = (int)($exerciseData['time_seconds'] ?? 0);
                        if ($hours > 0 || $minutes > 0 || $seconds > 0) {
                            $timeSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
                        }
                    } else {
                        // Fallback to time_seconds_actual if time breakdown not provided
                        $timeSeconds = $exerciseData['time_seconds_actual'] ?? null;
                    }
                    
                    WorkoutExercise::create([
                        'workout_log_id' => $workoutLog->id,
                        'day_exercise_id' => $dayExerciseId,
                        'name' => $exerciseData['name'],
                        'type' => $exerciseData['type'],
                        'sets_actual' => $exerciseData['sets_actual'] ?? null,
                        'reps_actual' => $exerciseData['reps_actual'] ?? null,
                        'weight_actual' => $exerciseData['weight_actual'] ?? null,
                        'distance_actual' => $exerciseData['distance_actual'] ?? null,
                        'time_seconds_actual' => $timeSeconds,
                        'notes' => $exerciseData['notes'] ?? null,
                        'order' => $order++,
                    ]);
                }
            }
            
            // Update progress after logging workout
            $activeProgram->refresh();
            $activeProgram->updateProgress();
        });
        
        // Get program for redirect
        $activeProgram = ActiveProgram::with('program')->findOrFail($this->activeProgramId);
        session()->flash('success', __('Workout logged successfully!'));
        $this->redirect(route('workouts.calendar', $activeProgram->program));
    }
}; ?>

<section class="w-full">
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/50 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/50 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
            {{ __('Log Workout') }}
        </h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Date: :date', ['date' => Carbon::parse($this->workoutDate)->format('M d, Y')]) }} | 
            {{ __('Program: :name', ['name' => $activeProgram->program->name]) }}
        </p>
    </div>

    <form wire:submit="save" class="space-y-6">
        @foreach($targetExercises as $targetExercise)
            @php
                $exerciseData = $exercises[$targetExercise->id] ?? [];
            @endphp
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $targetExercise->name }}
                    </h3>
                    <span class="mt-1 inline-block rounded-full bg-primary-100 dark:bg-primary-900/30 px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-300">
                        {{ ucfirst($targetExercise->type) }}
                    </span>
                </div>

                <!-- Target vs Actual Comparison -->
                <div class="mb-4 grid gap-4 md:grid-cols-2 rounded-lg bg-neutral-50 dark:bg-neutral-800/50 p-4">
                    <div>
                        <h4 class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-2">{{ __('Target') }}</h4>
                        <div class="space-y-1 text-sm">
                            @if($targetExercise->sets || ($targetExercise->sets_min && $targetExercise->sets_max))
                                <p>{{ __('Sets: :value', ['value' => ($targetExercise->sets_min && $targetExercise->sets_max) ? $targetExercise->sets_min . '-' . $targetExercise->sets_max : $targetExercise->sets]) }}</p>
                            @endif
                            @if($targetExercise->reps || ($targetExercise->reps_min && $targetExercise->reps_max))
                                <p>{{ __('Reps: :value', ['value' => ($targetExercise->reps_min && $targetExercise->reps_max) ? $targetExercise->reps_min . '-' . $targetExercise->reps_max : $targetExercise->reps]) }}</p>
                            @endif
                            @if($targetExercise->weight || ($targetExercise->weight_min && $targetExercise->weight_max))
                                <p>{{ __('Weight: :value lbs', ['value' => ($targetExercise->weight_min && $targetExercise->weight_max) ? $targetExercise->weight_min . '-' . $targetExercise->weight_max : $targetExercise->weight]) }}</p>
                            @endif
                            @if($targetExercise->distance || ($targetExercise->distance_min && $targetExercise->distance_max))
                                <p>{{ __('Distance: :value miles', ['value' => ($targetExercise->distance_min && $targetExercise->distance_max) ? $targetExercise->distance_min . '-' . $targetExercise->distance_max : $targetExercise->distance]) }}</p>
                            @endif
                            @if($targetExercise->time_seconds || ($targetExercise->time_seconds_min && $targetExercise->time_seconds_max))
                                <p>{{ __('Time: :value', ['value' => ($targetExercise->time_seconds_min && $targetExercise->time_seconds_max) ? gmdate('H:i:s', $targetExercise->time_seconds_min) . '-' . gmdate('H:i:s', $targetExercise->time_seconds_max) : gmdate('H:i:s', $targetExercise->time_seconds)]) }}</p>
                            @endif
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-2">{{ __('Actual') }}</h4>
                        <div class="space-y-3">
                            <flux:input 
                                wire:model="exercises.{{ $targetExercise->id }}.name"
                                :label="__('Exercise Name')"
                                placeholder="Exercise name"
                            />
                            
                            @php
                                $isStrength = $targetExercise->type === 'strength';
                                $isCardio = $targetExercise->type === 'cardio';
                                $isFlexibility = $targetExercise->type === 'flexibility';
                            @endphp
                            
                            @if($isStrength)
                                <flux:input 
                                    type="number"
                                    wire:model="exercises.{{ $targetExercise->id }}.sets_actual"
                                    :label="__('Sets')"
                                    placeholder="Sets"
                                    min="0"
                                />
                                
                                <flux:input 
                                    type="number"
                                    wire:model="exercises.{{ $targetExercise->id }}.reps_actual"
                                    :label="__('Reps')"
                                    placeholder="Reps"
                                    min="0"
                                />
                                
                                <flux:input 
                                    type="number"
                                    wire:model="exercises.{{ $targetExercise->id }}.weight_actual"
                                    :label="__('Weight (lbs)')"
                                    placeholder="Weight"
                                    step="0.01"
                                    min="0"
                                />
                            @elseif($isCardio)
                                <flux:input 
                                    type="number"
                                    wire:model="exercises.{{ $targetExercise->id }}.distance_actual"
                                    :label="__('Distance (miles)')"
                                    placeholder="Distance"
                                    step="0.01"
                                    min="0"
                                />
                                
                                <div>
                                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        {{ __('Time') }}
                                    </label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <flux:input 
                                            type="number"
                                            wire:model="exercises.{{ $targetExercise->id }}.time_hours"
                                            placeholder="Hours"
                                            min="0"
                                            max="23"
                                        />
                                        <flux:input 
                                            type="number"
                                            wire:model="exercises.{{ $targetExercise->id }}.time_minutes"
                                            placeholder="Minutes"
                                            min="0"
                                            max="59"
                                        />
                                        <flux:input 
                                            type="number"
                                            wire:model="exercises.{{ $targetExercise->id }}.time_seconds"
                                            placeholder="Seconds"
                                            min="0"
                                            max="59"
                                        />
                                    </div>
                                </div>
                            @else
                                {{-- For other types (flexibility, other), show all relevant fields --}}
                                @php
                                    $hasSets = $targetExercise->sets || ($targetExercise->sets_min && $targetExercise->sets_max);
                                    $hasReps = $targetExercise->reps || ($targetExercise->reps_min && $targetExercise->reps_max);
                                    $hasWeight = $targetExercise->weight || ($targetExercise->weight_min && $targetExercise->weight_max);
                                    $hasDistance = $targetExercise->distance || ($targetExercise->distance_min && $targetExercise->distance_max);
                                    $hasTime = $targetExercise->time_seconds || ($targetExercise->time_seconds_min && $targetExercise->time_seconds_max);
                                @endphp
                                
                                @if($hasSets)
                                    <flux:input 
                                        type="number"
                                        wire:model="exercises.{{ $targetExercise->id }}.sets_actual"
                                        :label="__('Sets')"
                                        placeholder="Sets"
                                        min="0"
                                    />
                                @endif
                                
                                @if($hasReps)
                                    <flux:input 
                                        type="number"
                                        wire:model="exercises.{{ $targetExercise->id }}.reps_actual"
                                        :label="__('Reps')"
                                        placeholder="Reps"
                                        min="0"
                                    />
                                @endif
                                
                                @if($hasWeight)
                                    <flux:input 
                                        type="number"
                                        wire:model="exercises.{{ $targetExercise->id }}.weight_actual"
                                        :label="__('Weight (lbs)')"
                                        placeholder="Weight"
                                        step="0.01"
                                        min="0"
                                    />
                                @endif
                                
                                @if($hasDistance)
                                    <flux:input 
                                        type="number"
                                        wire:model="exercises.{{ $targetExercise->id }}.distance_actual"
                                        :label="__('Distance (miles)')"
                                        placeholder="Distance"
                                        step="0.01"
                                        min="0"
                                    />
                                @endif
                                
                                @if($hasTime)
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                            {{ __('Time') }}
                                        </label>
                                        <div class="grid grid-cols-3 gap-2">
                                            <flux:input 
                                                type="number"
                                                wire:model="exercises.{{ $targetExercise->id }}.time_hours"
                                                placeholder="Hours"
                                                min="0"
                                                max="23"
                                            />
                                            <flux:input 
                                                type="number"
                                                wire:model="exercises.{{ $targetExercise->id }}.time_minutes"
                                                placeholder="Minutes"
                                                min="0"
                                                max="59"
                                            />
                                            <flux:input 
                                                type="number"
                                                wire:model="exercises.{{ $targetExercise->id }}.time_seconds"
                                                placeholder="Seconds"
                                                min="0"
                                                max="59"
                                            />
                                        </div>
                                    </div>
                                @endif
                            @endif
                            
                            <flux:textarea 
                                wire:model="exercises.{{ $targetExercise->id }}.notes"
                                :label="__('Exercise Notes (optional)')"
                                placeholder="Add any notes about this exercise..."
                                rows="2"
                            />
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <flux:textarea 
                wire:model="notes" 
                :label="__('Workout Notes')" 
                placeholder="Overall workout notes..."
                rows="3"
            />
        </div>

        <div class="flex items-center gap-4 border-t border-neutral-200 dark:border-neutral-700 pt-6">
            <flux:button type="submit" variant="primary" class="w-full md:w-auto">
                {{ $workoutLogId ? __('Update Workout') : __('Save Workout') }}
            </flux:button>

            <flux:button href="{{ route('workouts.calendar') }}" variant="ghost" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</section>

