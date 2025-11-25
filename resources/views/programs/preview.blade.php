<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $program->name }} - Preview</title>
    <style>
        @media print {
            .no-print { display: none; }
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .program-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .program-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .meta-item {
            font-size: 0.9rem;
        }
        .meta-label {
            font-weight: 600;
            color: #6b7280;
        }
        .week-section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        .week-header {
            background-color: #f3f4f6;
            padding: 15px;
            border-left: 4px solid #3b82f6;
            margin-bottom: 20px;
            font-size: 1.25rem;
            font-weight: 600;
        }
        .day-section {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            page-break-inside: avoid;
        }
        .day-header {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1f2937;
        }
        .exercise-item {
            margin-bottom: 15px;
            padding: 12px;
            background-color: #f9fafb;
            border-left: 3px solid #10b981;
            border-radius: 4px;
        }
        .exercise-name {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 8px;
        }
        .exercise-type {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .exercise-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 8px;
        }
        .detail-item {
            font-size: 0.9rem;
        }
        .detail-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 0.85rem;
        }
        .notes-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
        }
        .actions {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }
        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }
    </style>
</head>
<body>
    <div class="no-print actions">
        <a href="{{ route('programs.show', $program) }}" class="btn btn-secondary">← Back</a>
        <a href="{{ route('programs.export-pdf', $program) }}" class="btn btn-primary" target="_blank">Download PDF</a>
        <button onclick="window.print()" class="btn btn-primary">Print</button>
    </div>

    <div class="header">
        <h1 class="program-title">{{ $program->name }}</h1>
        @if($program->description)
            <p style="color: #6b7280; margin-top: 10px;">{{ $program->description }}</p>
        @endif
        
        <div class="program-meta">
            <div class="meta-item">
                <span class="meta-label">Length:</span>
                <span>{{ $program->length_weeks }} weeks</span>
            </div>
            @if($program->start_date)
                <div class="meta-item">
                    <span class="meta-label">Start Date:</span>
                    <span>{{ $program->start_date->format('M d, Y') }}</span>
                </div>
            @endif
            @if($program->end_date)
                <div class="meta-item">
                    <span class="meta-label">End Date:</span>
                    <span>{{ $program->end_date->format('M d, Y') }}</span>
                </div>
            @endif
            <div class="meta-item">
                <span class="meta-label">Generated:</span>
                <span>{{ now()->format('M d, Y') }}</span>
            </div>
        </div>
    </div>

    @if($program->weeks->isEmpty())
        <p style="text-align: center; color: #6b7280; padding: 40px;">
            No weeks have been added to this program yet.
        </p>
    @else
        @foreach($program->weeks->sortBy('week_number') as $week)
            <div class="week-section">
                <div class="week-header">
                    Week {{ $week->week_number }}
                </div>

                @foreach($week->days->sortBy('day_number') as $day)
                    <div class="day-section">
                        <div class="day-header">
                            {{ $day->label ?: 'Day ' . $day->day_number }}
                        </div>

                        @if($day->exercises->isEmpty())
                            <p style="color: #9ca3af; font-style: italic;">No exercises for this day.</p>
                        @else
                            @foreach($day->exercises->sortBy('order') as $exercise)
                                <div class="exercise-item">
                                    <div class="exercise-name">{{ $exercise->name }}</div>
                                    <span class="exercise-type">{{ ucfirst($exercise->type) }}</span>
                                    
                                    <div class="exercise-details">
                                        @if($exercise->sets)
                                            <div class="detail-item">
                                                <div class="detail-label">Sets</div>
                                                <div>{{ $exercise->sets }}</div>
                                            </div>
                                        @endif
                                        @if($exercise->reps)
                                            <div class="detail-item">
                                                <div class="detail-label">Reps</div>
                                                <div>{{ $exercise->reps }}</div>
                                            </div>
                                        @endif
                                        @if($exercise->weight)
                                            <div class="detail-item">
                                                <div class="detail-label">Weight</div>
                                                <div>{{ $exercise->weight }} lbs</div>
                                            </div>
                                        @endif
                                        @if($exercise->distance)
                                            <div class="detail-item">
                                                <div class="detail-label">Distance</div>
                                                <div>{{ $exercise->distance }} miles</div>
                                            </div>
                                        @endif
                                        @if($exercise->time_seconds)
                                            <div class="detail-item">
                                                <div class="detail-label">Time</div>
                                                <div>{{ gmdate('H:i:s', $exercise->time_seconds) }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif

    @if($program->notes)
        <div class="notes-section">
            <div style="font-weight: 600; margin-bottom: 8px;">Notes:</div>
            <div>{{ $program->notes }}</div>
        </div>
    @endif
</body>
</html>

