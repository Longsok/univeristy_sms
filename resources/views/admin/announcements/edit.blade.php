@extends('layouts.admin')
@section('title', 'Edit Announcement')
@section('page-title', 'Edit Announcement')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Edit Announcement</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.announcements.index') }}">Announcements</a> / Edit
        </div>
    </div>
</div>

<div style="max-width:700px;">
    <div class="card-rupp">
        <div class="rupp-header-strip">
            <i class="bi bi-pencil-fill"></i>
            <h5>Edit Announcement</h5>
        </div>
        <div class="card-rupp-body">
            <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Title <span style="color:#ef4444">*</span></label>
                    <input type="text" name="title"
                        value="{{ old('title', $announcement->title) }}"
                        class="form-control-rupp"
                        placeholder="Announcement title"
                        required>
                    @error('title')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Message <span style="color:#ef4444">*</span></label>
                    <textarea name="body" rows="6"
                        class="form-control-rupp"
                        placeholder="Write your announcement here..."
                        required>{{ old('body', $announcement->body) }}</textarea>
                    @error('body')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="form-label-rupp">Target Audience <span style="color:#ef4444">*</span></label>
                        <select name="target_role" class="form-select-rupp" required>
                            <option value="all"     {{ old('target_role', $announcement->target_role) === 'all'     ? 'selected' : '' }}>Everyone</option>
                            <option value="student" {{ old('target_role', $announcement->target_role) === 'student' ? 'selected' : '' }}>Students Only</option>
                            <option value="teacher" {{ old('target_role', $announcement->target_role) === 'teacher' ? 'selected' : '' }}>Teachers Only</option>
                        </select>
                    </div>
                    <div style="display:flex; align-items:flex-end; padding-bottom:2px;">
                        <div>
                            <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Status</div>
                            @if($announcement->published_at)
                                <span class="badge-rupp badge-green">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Published {{ $announcement->published_at->format('d M Y') }}
                                </span>
                            @else
                                <span class="badge-rupp badge-gray">Draft — not published</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Existing attachment --}}
                @if($announcement->attachment)
                <div style="margin-bottom:16px; background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:12px 16px;">
                    <div style="font-size:12px; color:#166534; font-weight:600; margin-bottom:8px;">
                        <i class="bi bi-paperclip"></i> Current Attachment
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <a href="{{ Storage::url($announcement->attachment) }}" target="_blank"
                           style="font-size:13px; color:#166534; text-decoration:none; display:flex; align-items:center; gap:6px;">
                            <i class="bi bi-file-earmark-fill"></i>
                            {{ $announcement->attachment_name ?? 'Download File' }}
                        </a>
                        <label style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:#991b1b; cursor:pointer;">
                            <input type="checkbox" name="remove_attachment" value="1"
                                style="accent-color:#dc2626;">
                            Remove attachment
                        </label>
                    </div>
                </div>
                @endif

                {{-- File attachment upload --}}
                <div style="margin-bottom:20px;">
                    <label class="form-label-rupp">
                        {{ $announcement->attachment ? 'Replace Attachment' : 'Add Attachment' }} (optional)
                    </label>
                    <div style="border:2px dashed #d1d5db; border-radius:10px; padding:20px; text-align:center; cursor:pointer; transition:border-color .15s;"
                         onclick="document.getElementById('attachmentInput').click()"
                         id="dropZone"
                         ondragover="event.preventDefault(); this.style.borderColor='var(--rupp-green)'"
                         ondragleave="this.style.borderColor='#d1d5db'"
                         ondrop="handleDrop(event)">
                        <i class="bi bi-cloud-upload" style="font-size:28px; color:#9ca3af; display:block; margin-bottom:8px;"></i>
                        <div style="font-size:13.5px; color:#6b7280;">Click to upload or drag and drop</div>
                        <div style="font-size:12px; color:#9ca3af; margin-top:4px;">PDF, DOC, DOCX, JPG, PNG up to 5MB</div>
                        <div id="fileName" style="margin-top:8px; font-size:13px; color:var(--rupp-green); font-weight:500;"></div>
                    </div>
                    <input type="file" id="attachmentInput" name="attachment"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        style="display:none;"
                        onchange="showFileName(this)">
                    @error('attachment')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex; gap:12px;">
                    <button type="submit" class="btn-rupp-primary">
                        <i class="bi bi-floppy-fill"></i> Update Announcement
                    </button>
                    @if(!$announcement->published_at)
                    <form action="{{ route('admin.announcements.publish', $announcement) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-rupp-gold">
                            <i class="bi bi-send-fill"></i> Publish Now
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('admin.announcements.index') }}" class="btn-rupp-outline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showFileName(input) {
    const el = document.getElementById('fileName');
    if (input.files && input.files[0]) {
        el.textContent = '📎 ' + input.files[0].name;
        document.getElementById('dropZone').style.borderColor = 'var(--rupp-green)';
    }
}
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropZone').style.borderColor = '#d1d5db';
    const files = e.dataTransfer.files;
    if (files.length) {
        document.getElementById('attachmentInput').files = files;
        document.getElementById('fileName').textContent = '📎 ' + files[0].name;
        document.getElementById('dropZone').style.borderColor = 'var(--rupp-green)';
    }
}
</script>
@endpush