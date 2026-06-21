@extends('layouts.teacher')
@section('title', 'Announcements')
@section('page-title', 'Announcements')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Announcements</h1>
        <div class="breadcrumb-text">Notices from the university</div>
    </div>
</div>

@forelse($announcements as $ann)
<div class="card-rupp" style="margin-bottom:14px;">
    <div style="padding:16px 20px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
            <div style="flex:1;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                    <span class="badge-rupp {{ $ann->target_role === 'all' ? 'badge-blue' : ($ann->target_role === 'teacher' ? 'badge-green' : 'badge-gold') }}" style="font-size:10px;">
                        {{ $ann->target_role === 'all' ? 'Everyone' : ucfirst($ann->target_role) }}
                    </span>
                    <span style="font-size:12px; color:#9ca3af;">
                        <i class="bi bi-clock"></i>
                        {{ $ann->published_at->diffForHumans() }}
                    </span>
                </div>
                <div style="font-size:16px; font-weight:600; color:#111827; margin-bottom:8px;">
                    {{ $ann->title }}
                </div>
                <div style="font-size:13.5px; color:#6b7280; line-height:1.6;">
                    {!! nl2br(e($ann->body)) !!}
                </div>
            </div>
        </div>

        {{-- Attachment --}}
        @if($ann->attachment)
        <div style="margin-top:14px; padding-top:12px; border-top:1px solid #f3f4f6;">
            <a href="{{ Storage::url($ann->attachment) }}" target="_blank"
               style="display:inline-flex; align-items:center; gap:8px; background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:8px 14px; font-size:13px; color:#166534; text-decoration:none; font-weight:500;">
                <i class="bi bi-paperclip"></i>
                {{ $ann->attachment_name ?? 'Download Attachment' }}
            </a>
        </div>
        @endif

        <div style="margin-top:12px; font-size:11.5px; color:#9ca3af;">
            Posted by {{ $ann->admin->name }} · {{ $ann->published_at->format('d M Y, H:i') }}
        </div>
    </div>
</div>
@empty
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px; color:#9ca3af;">
        <i class="bi bi-megaphone" style="font-size:40px; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500;">No announcements yet.</div>
        <div style="font-size:13px; margin-top:4px;">Check back later for updates from the university.</div>
    </div>
</div>
@endforelse

{{ $announcements->links() }}
@endsection