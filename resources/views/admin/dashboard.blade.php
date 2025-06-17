@php
    $currentSort = request('sort');
    $currentDirection = request('direction') ?? 'asc';

    function sortIcon($column) {
        $sort = request('sort');
        $direction = request('direction', 'asc');
        if ($sort === $column) {
            return $direction === 'asc' ? '▲' : '▼';
        }
        return '↕';
    }

    function sortLink($column, $label) {
        $currentSort = request('sort');
        $currentDirection = request('direction') ?? 'asc';
        $nextDirection = ($currentSort === $column && $currentDirection === 'asc') ? 'desc' : 'asc';
        $search = request('search');
        $url = url()->current() . "?sort={$column}&direction={$nextDirection}&search={$search}";
        return "<a href=\"{$url}\">{$label} <span class=\"sort-icon\">" . sortIcon($column) . "</span></a>";
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="pg-header-bar">
            <div class="pg-header-container">
                <h1 class="pg-header-title">{{ __('Admin Panel') }}</h1>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="pg-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
        </div>
    @endif

    <div class="container py-4">
        <div class="row mb-4 justify-content-between align-items-center">
            <div class="col-md-7 pg-search">
                <form method="GET" action="{{ route('admin.dashboard') }}">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="{{ __('Search user...') }}" value="{{ request('search') }}">
                        <button class="btn" type="submit">{{ __('Search') }}</button>
                    </div>
                </form>
            </div>
            <div class="col-md-auto mt-3 mt-md-0 text-md-end">
                <a href="{{ route('admin.createUser') }}" class="btn btn-success">
                    <i class="bi bi-person-plus"></i> {{ __('Add user') }}
                </a>
            </div>
        </div>

        <div class="card pg-card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center pg-table">
                        <thead>
                            <tr>
                                <th>{!! sortLink('name', __('Name')) !!}</th>
                                <th>{!! sortLink('last_name', __('Surname')) !!}</th>
                                <th>{!! sortLink('email', __('Email')) !!}</th>
                                <th>{!! sortLink('role', __('Role')) !!}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->last_name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ __(ucfirst($user->role)) }}</td>
                                    <td>
                                        <a href="{{ route('admin.editUser', $user->id) }}" class="btn btn-sm pg-btn-outline me-1">
                                            {{ __('Edit') }}
                                        </a>
                                        @if(auth()->id() !== $user->id)
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}">
                                                {{ __('Delete') }}
                                            </button>

                                            <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $user->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ __('Confirmation of deletion') }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            {{ __('Are you sure you want to delete the user') }} <strong>{{ $user->name }} {{ $user->last_name }}</strong>?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                            <form action="{{ route('admin.deleteUser', $user->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-danger">{{ __('Delete') }}</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="pg-muted py-4">{{ __('No users to display.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 px-3">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>