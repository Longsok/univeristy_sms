@extends('layouts.admin')
@section('title', 'Grade Management')
@section('page-title', 'Grade Management')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Grade Management</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Grades
        </div>
    </div>
</div>

{{-- Info banner --}}
<div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:14px 18px; margin-bottom:20px; display:flex; gap:12px; align-items:flex-start;">
    <i class="bi bi-lock-fill" style="color:#c2410c; font-size:18px; flex-shrink:0;"></i>
    <div style="font-size:13px; color:#9a3412; line-height:1.7;">
        <strong>Grade Lock Policy:</strong>
        Once a teacher finalises grades, they are locked and cannot be edited.
        Only administrators can unlock grades. Use this carefully —
        unlocking allows the teacher to modify previously submitted grades.
    </div>
</div>

<div class="card-rupp">
    <div style="overflow-x:auto;">
        <table class="table-rupp">
            <thead>
                <tr>
                    <th>Section</th>
                    <th>Course</th>
                    <th>Teacher</th>
                    <th style="text-align:center;">Students</th>
                    <th style="text-align:center;">Finalised</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sections as $section)
                @php
                    $allFinalised  = $section->finalised_count > 0 && $section->unfinalised_count === 0;
                    $partialised   = $section->finalised_count > 0 && $section->unfinalised_count > 0;
                    $noneFinalised = $section->finalised_count === 0;
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:600; color:#111827;">{{ $section->name }}</div>
                        <div style="font-size:11px; color:#9ca3af;">
                            {{ $section->course->program?->code }}
                            · Year {{ $section->course->year_level }}
                        </div>
                    </td>
                    <td>
                        <div style="font-size:13px; color:#374151;">{{ $section->course->name }}</div>
                        <div style="font-size:11px; color:#9ca3af; font-family:monospace;">{{ $section->course->code }}</div>
                    </td>
                    <td style="font-size:13px; color:#374151;">
                        {{ $section->teacher?->user?->name ?? '—' }}
                    </td>
                    <td style="text-align:center; font-weight:600;">
                        {{ $section->total_students }}
                    </td>
                    <td style="text-align:center;">
                        <span style="font-weight:600; color:{{ $section->finalised_count > 0 ? '#166534' : '#9ca3af' }};">
                            {{ $section->finalised_count }}
                        </span>
                        <span style="color:#9ca3af;"> / {{ $section->total_students }}</span>
                    </td>
                    <td style="text-align:center;">
                        @if($allFinalised)
                        <span class="badge-rupp badge-green">
                            <i class="bi bi-lock-fill" style="font-size:10px;"></i> All Finalised
                        </span>
                        @elseif($partialised)
                        <span style="background:#fff7ed; color:#c2410c; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:600;">
                            Partial
                        </span>
                        @else
                        <span class="badge-rupp badge-gray">
                            Not Finalised
                        </span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @if($section->finalised_count > 0)
                        <form action="{{ route('admin.grades.unlock', $section) }}" method="POST"
                            onsubmit="return confirm('Unlock grades for {{ addslashes($section->name) }}?\n\nThis allows the teacher to edit previously finalised grades.\n\nAre you sure?')">
                            @csrf
                            <button type="submit"
                                style="background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; border-radius:8px; padding:5px 14px; font-size:12px; cursor:pointer; font-weight:600; display:inline-flex; align-items:center; gap:5px;">
                                <i class="bi bi-unlock-fill"></i> Unlock
                            </button>
                        </form>
                        @else
                        <span style="font-size:12px; color:#d1d5db;">No grades yet</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding:40px; color:#9ca3af;">
                        No sections found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection