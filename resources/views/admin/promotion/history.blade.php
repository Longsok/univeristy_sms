@extends('layouts.admin')
@section('title', 'Promotion History')
@section('page-title', 'Promotion History')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Promotion History</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.promotion.index') }}">Year Promotion</a> /
            History
        </div>
    </div>
    <a href="{{ route('admin.promotion.index') }}" class="btn-rupp-outline">
        <i class="bi bi-arrow-left"></i> Back to Promotion
    </a>
</div>

{{-- Filter --}}
<div class="card-rupp" style="margin-bottom:20px;">
    <div class="card-rupp-body" style="padding:14px 20px;">
        <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <select name="type" class="form-select-rupp" style="min-width:160px;" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="promotion"  {{ request('type') === 'promotion'  ? 'selected' : '' }}>Promotion</option>
                <option value="graduation" {{ request('type') === 'graduation' ? 'selected' : '' }}>Graduation</option>
            </select>
            <select name="academic_year" class="form-select-rupp" style="min-width:160px;" onchange="this.form.submit()">
                <option value="">All Academic Years</option>
                @foreach($academicYears as $year)
                <option value="{{ $year }}" {{ request('academic_year') === $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
                @endforeach
            </select>
            @if(request('type') || request('academic_year'))
            <a href="{{ route('admin.promotion.history') }}" class="btn-rupp-outline" style="padding:7px 14px;">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="card-rupp">
    <div style="overflow-x:auto;">
        <table class="table-rupp">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Class Group</th>
                    <th style="text-align:center;">From → To</th>
                    <th style="text-align:center;">GPA</th>
                    <th>Academic Year</th>
                    <th>Type</th>
                    <th>Promoted By</th>
                    <th>Notes</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $h)
                <tr>
                    <td style="color:#9ca3af; font-size:12px;">{{ $history->firstItem() + $loop->index }}</td>
                    <td>
                        <div style="font-weight:500;">{{ $h->student->user->name }}</div>
                        @if($h->student->user->name_kh)
                        <div style="font-size:11px; color:#9ca3af; font-family:'Hanuman',serif;">{{ $h->student->user->name_kh }}</div>
                        @endif
                        <div style="font-size:11px; color:#9ca3af; font-family:monospace;">{{ $h->student->student_id }}</div>
                    </td>
                    <td>
                        @if($h->classGroup)
                        <div style="font-weight:600; color:var(--rupp-green);">{{ $h->classGroup->name }}</div>
                        <div style="font-size:11px; color:#9ca3af;">{{ $h->classGroup->program->code ?? '' }}</div>
                        @else
                        <span style="color:#9ca3af; font-size:12px;">Ungrouped</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <div style="display:inline-flex; align-items:center; gap:6px;">
                            <span style="background:#f3f4f6; border-radius:6px; padding:3px 10px; font-weight:600; font-size:12px;">
                                Year {{ $h->from_year }}
                            </span>
                            <i class="bi bi-arrow-right" style="color:#9ca3af;"></i>
                            <span style="background:{{ $h->type === 'graduation' ? 'var(--rupp-gold-pale)' : '#dcfce7' }}; border-radius:6px; padding:3px 10px; font-weight:600; font-size:12px; color:{{ $h->type === 'graduation' ? '#92680a' : '#166534' }};">
                                {{ $h->type === 'graduation' ? '🎓 Graduated' : 'Year '.$h->to_year }}
                            </span>
                        </div>
                    </td>
                    <td style="text-align:center; font-weight:700; color:{{ $h->gpa_at_promotion >= 3.0 ? 'var(--rupp-green)' : ($h->gpa_at_promotion >= 2.0 ? '#c2410c' : '#6b7280') }};">
                        {{ number_format($h->gpa_at_promotion, 2) }}
                    </td>
                    <td style="font-size:12.5px; color:#6b7280;">{{ $h->academic_year }}</td>
                    <td>
                        <span class="badge-rupp {{ $h->type === 'graduation' ? 'badge-gold' : 'badge-green' }}" style="font-size:11px;">
                            {{ ucfirst($h->type) }}
                        </span>
                    </td>
                    <td style="font-size:12.5px; color:#6b7280;">{{ $h->promotedBy->name }}</td>
                    <td style="font-size:12px; color:#9ca3af; max-width:150px;">{{ $h->notes ?? '—' }}</td>
                    <td style="font-size:11.5px; color:#9ca3af; white-space:nowrap;">
                        {{ $h->created_at->format('d M Y') }}<br>
                        <span style="font-size:10px;">{{ $h->created_at->format('H:i') }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center; padding:40px; color:#9ca3af;">
                        <i class="bi bi-clock-history" style="font-size:32px; display:block; margin-bottom:10px;"></i>
                        No promotion records yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($history->hasPages())
    <div style="padding:16px 20px; border-top:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
        <div style="font-size:13px; color:#6b7280;">
            Showing {{ $history->firstItem() }}–{{ $history->lastItem() }} of {{ $history->total() }} records
        </div>
        {{ $history->links() }}
    </div>
    @endif
</div>
@endsection