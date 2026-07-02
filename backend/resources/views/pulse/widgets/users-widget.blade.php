<div class="card">
    <div class="card-header">
        <h5>👤 User Safety Status</h5>
    </div>
    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Score</th>
                    <th>Level</th>
                    <th>Trend</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users ?? [] as $userData)
                    <tr>
                        <td>{{ $userData['user']->name }}</td>
                        <td>
                            <span class="badge bg-{{ $userData['level_config']['color'] }}">
                                {{ $userData['score'] }}
                            </span>
                        </td>
                        <td>
                            {{ $userData['level_config']['icon'] }} 
                            {{ $userData['level_config']['label'] }}
                        </td>
                        <td>
                            @if($userData['trend'] === 'improving')
                                <span class="text-success">↑</span>
                            @elseif($userData['trend'] === 'declining')
                                <span class="text-danger">↓</span>
                            @else
                                <span class="text-muted">→</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
