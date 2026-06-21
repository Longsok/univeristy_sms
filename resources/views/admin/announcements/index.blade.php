@extends('layouts.admin')
@section('title', 'Announcements')
@section('page-title', 'Announcements')
 
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Announcements</h1>
        <div class="breadcrumb-text"><a href="{{ route('admin.dashboard') }}">Dashboard</a> / Announcements</div>
    </div>
    <a href="{{ route('admin.announcements.create') }}" class="btn-rupp-primary">
        <i class="bi bi-plus-lg"></i> New Announcement
    </a>
</div>
 
<div class="card-rupp">
    <div style="overflow-x:auto;">
        <table class="table-rupp">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Target</th>
                    <th>Posted By</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($announcements as $ann)
                <tr>
                    <td>
                        <div style="font-weight:500;">{{ $ann->title }}</div>
                        <div style="font-size:12px;color:#9ca3af;">{{ Str::limit($ann->body, 60) }}</div>
                    </td>
                    <td>
                        <span class="badge-rupp {{ $ann->target_role === 'all' ? 'badge-blue' : ($ann->target_role === 'student' ? 'badge-green' : 'badge-gold') }}">
                            {{ ucfirst($ann->target_role) }}
                        </span>
                    </td>
                    <td style="font-size:12.5px;color:#6b7280;">{{ $ann->admin->name }}</td>
                    <td>
                        @if($ann->published_at)
                            <span class="badge-rupp badge-green">Published</span>
                        @else
                            <span class="badge-rupp badge-gray">Draft</span>
                        @endif
                    </td>
                    <td style="font-size:12.5px;color:#6b7280;">
                        {{ $ann->published_at ? $ann->published_at->format('d M Y') : '—' }}
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="{{ route('admin.announcements.edit', $ann) }}" class="btn-icon edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            @if(!$ann->published_at)
                            <form action="{{ route('admin.announcements.publish', $ann) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-icon view" title="Publish">
                                    <i class="bi bi-send-fill"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('admin.announcements.destroy', $ann) }}" method="POST"
                                onsubmit="return confirm('Delete this announcement?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:40px;color:#9ca3af;">No announcements yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($announcements->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #f3f4f6;">{{ $announcements->links() }}</div>
    @endif
</div>
@endsection