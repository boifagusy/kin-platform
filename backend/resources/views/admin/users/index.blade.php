@extends('layouts.admin')

@section('title', 'User Management')
@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">User Management</h1>
            <p class="text-sm text-gray-500 mt-1">Monitor user safety and activity</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-2xl font-bold text-gray-800">{{ number_format($stats['total'] ?? 0) }}</div>
            <div class="text-xs text-gray-500">Total Users</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-2xl font-bold text-green-600">{{ number_format($stats['active'] ?? 0) }}</div>
            <div class="text-xs text-gray-500">Active (7d)</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['new_7d'] ?? 0) }}</div>
            <div class="text-xs text-gray-500">New (7d)</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-yellow-500">
            <div class="text-2xl font-bold text-yellow-700">{{ number_format($stats['missed_30d'] ?? 0) }}</div>
            <div class="text-xs text-gray-500">Missed Check-ins</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-orange-500">
            <div class="text-2xl font-bold text-orange-700">{{ number_format($stats['duress_30d'] ?? 0) }}</div>
            <div class="text-xs text-gray-500">Duress Usage</div>
        </div>
        <div class="bg-red-50 rounded-xl p-4 border border-red-200">
            <div class="text-2xl font-bold text-red-600">{{ number_format($stats['high_risk'] ?? 0) }}</div>
            <div class="text-xs text-red-600 font-medium">High Risk</div>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or phone..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none">
            </div>
            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-container transition-colors">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.users.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-center">Clear</a>
            @endif
        </form>
    </div>

    <!-- Bulk Action Form -->
    <form method="POST" action="{{ route('admin.users.bulk') }}" id="bulkForm">
        @csrf
        <input type="hidden" name="action" id="bulkActionInput" value="">

        <!-- Users Table -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAllCheckbox">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Safety Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Check-in</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Missed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacts</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verified</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="userCheckbox">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $user->name ?? 'Unknown' }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        @if($user->phone)
                                            <div class="text-xs text-gray-400">{{ $user->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->status == 'active')
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                                @elseif($user->status == 'suspended')
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Suspended</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php $score = $user->safety_score ?? 100; @endphp
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $score >= 80 ? 'bg-green-500' : ($score >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $score }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium">{{ $score }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($user->last_checkin?->checked_in_at)
                                    {{ $user->last_checkin->checked_in_at->diffForHumans() }}
                                @elseif($user->last_checkin_at)
                                    {{ \Carbon\Carbon::parse($user->last_checkin_at)->diffForHumans() }}
                                @else
                                    Never
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span class="{{ ($user->missed_checkins_30d ?? 0) > 0 ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                    {{ $user->missed_checkins_30d ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span class="{{ ($user->duress_count ?? 0) > 0 ? 'text-orange-600 font-bold' : 'text-gray-500' }}">
                                    {{ $user->duress_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span class="{{ ($user->trusted_contacts_count ?? 0) > 0 ? 'text-green-600 font-bold' : 'text-gray-500' }}">
                                    {{ $user->trusted_contacts_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span class="{{ ($user->verified_contacts_count ?? 0) > 0 ? 'text-green-600 font-bold' : 'text-gray-500' }}">
                                    {{ $user->verified_contacts_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="text-primary hover:underline text-sm font-medium flex items-center gap-1">
                                    View <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-500">No users found matching your criteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bulk Actions -->
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span id="selectedCount" class="text-sm text-gray-600">0 selected</span>
                </div>
                <div class="flex gap-2">
                    <select id="bulkActionSelect" class="border border-gray-300 rounded-lg px-3 py-1 text-sm">
                        <option value="">Bulk Actions</option>
                        <option value="activate">Activate</option>
                        <option value="suspend">Suspend</option>
                        <option value="delete">Delete</option>
                    </select>
                    <button type="button" id="applyBulkBtn" class="bg-primary text-white px-4 py-1 rounded-lg text-sm">Apply</button>
                </div>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </form>
</div>

<script>
    // Pure JavaScript - no Alpine.js
    (function() {
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const applyBtn = document.getElementById('applyBulkBtn');
        const bulkActionSelect = document.getElementById('bulkActionSelect');
        const selectedCountSpan = document.getElementById('selectedCount');
        const bulkForm = document.getElementById('bulkForm');
        const bulkActionInput = document.getElementById('bulkActionInput');
        
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.userCheckbox');
            const checkedCount = document.querySelectorAll('.userCheckbox:checked').length;
            if (selectedCountSpan) {
                selectedCountSpan.textContent = checkedCount + ' selected';
            }
            if (selectAllCheckbox && checkboxes.length > 0) {
                selectAllCheckbox.checked = (checkboxes.length === checkedCount);
            }
        }
        
        // Select All
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('click', function(e) {
                const isChecked = e.target.checked;
                document.querySelectorAll('.userCheckbox').forEach(function(cb) {
                    cb.checked = isChecked;
                });
                updateSelectedCount();
            });
        }
        
        // Individual checkboxes
        document.querySelectorAll('.userCheckbox').forEach(function(cb) {
            cb.addEventListener('change', updateSelectedCount);
        });
        
        // Apply button
        if (applyBtn) {
            applyBtn.addEventListener('click', function() {
                const action = bulkActionSelect ? bulkActionSelect.value : '';
                const selectedIds = [];
                document.querySelectorAll('.userCheckbox:checked').forEach(function(cb) {
                    selectedIds.push(cb.value);
                });
                
                if (!action) {
                    alert('Please select an action (Activate, Suspend, or Delete)');
                    return;
                }
                
                if (selectedIds.length === 0) {
                    alert('Please select at least one user');
                    return;
                }
                
                let confirmMsg = '';
                if (action === 'activate') {
                    confirmMsg = 'Activate ' + selectedIds.length + ' user(s)?';
                } else if (action === 'suspend') {
                    confirmMsg = 'Suspend ' + selectedIds.length + ' user(s)?';
                } else if (action === 'delete') {
                    confirmMsg = 'Delete ' + selectedIds.length + ' user(s)? This can be restored later.';
                }
                
                if (confirm(confirmMsg)) {
                    bulkActionInput.value = action;
                    bulkForm.submit();
                }
            });
        }
        
        // Initial count
        updateSelectedCount();
    })();
</script>
@endsection
