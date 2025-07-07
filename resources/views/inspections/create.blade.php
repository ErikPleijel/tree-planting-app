<x-app-layout>
    <div class="container">
        <h1>Add Inspection</h1>

        <form method="POST" action="{{ route('inspections.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Inspection Date</label>
                <input type="date" name="inspection_date" class="form-control" value="{{ old('inspection_date') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Verified</label>
                <select name="verified" class="form-select" required>
                    <option value="">-- Select --</option>
                    <option value="1" @if(old('verified')==="1") selected @endif>Yes</option>
                    <option value="0" @if(old('verified')==="0") selected @endif>No</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Comment</label>
                <textarea name="comment" class="form-control">{{ old('comment') }}</textarea>
            </div>

            <button class="btn btn-primary">Save Inspection</button>
            <a href="{{ route('inspections.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</x-app-layout>
