<x-app-layout>
    <div class="container">
        <h1>Inspections</h1>

        <a href="{{ route('inspections.create') }}" class="btn btn-primary mb-3">Add New Inspection</a>

        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Monitor</th>
                <th>Verified</th>
                <th>Comment</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($inspections as $inspection)
                <tr>
                    <td>{{ $inspection->id }}</td>
                    <td>{{ $inspection->inspection_date }}</td>
                    <td>{{ $inspection->user->name ?? 'N/A' }}</td>
                    <td>{{ $inspection->verified ? 'Yes' : 'No' }}</td>
                    <td>{{ $inspection->comment }}</td>
                    <td>
                        <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('inspections.destroy', $inspection) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Delete this inspection?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
