<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('view-all-users.title') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <x-admin-nav></x-admin-nav>

    <div class="container-fluid">

        <h1 class="lexend fw-bold text-center mt-5 mb-5">{{ __('view-all-users.heading') }}</h1>

        @if ($users->isEmpty())
            <h3 class="text-center fw-bold lexend mt-5" style="margin-bottom: 130px">{{ __('view-all-users.no_data') }}</h3>
        @else
        <table class="table text-center" style="margin-bottom: 130px">
            <thead>
                <tr>
                    <th scope="col" style="background-color: rgb(165, 203, 165) !important">{{ __('view-all-users.no') }}</th>
                    <th scope="col" style="background-color: rgb(165, 203, 165) !important">{{ __('view-all-users.id') }}</th>
                    <th scope="col" style="background-color: rgb(165, 203, 165) !important">{{ __('view-all-users.username') }}</th>
                    <th scope="col" style="background-color: rgb(165, 203, 165) !important">{{ __('view-all-users.email') }}</th>
                    <th scope="col" style="background-color: rgb(165, 203, 165) !important">{{ __('view-all-users.role') }}</th>
                    <th scope="col" style="background-color: rgb(165, 203, 165) !important">{{ __('view-all-users.created_at') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ $user->usersId }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role }}</td>
                        <td>{{ $user->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($users->lastPage() > 1)
            <ul class="catering-pagination pagination justify-content-center my-3">
                <li class="page-item {{ $users->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $users->previousPageUrl() ?? '#' }}" tabindex="-1">&laquo;</a>
                </li>

                @for ($i = 1; $i <= $users->lastPage(); $i++)
                    <li class="page-item {{ $users->currentPage() == $i ? 'active' : '' }}">
                        <a class="page-link" href="{{ $users->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor

                <li class="page-item {{ !$users->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $users->nextPageUrl() ?? '#' }}">&raquo;</a>
                </li>
            </ul>
        @endif

        @endif

    </div>

    <x-admin-footer></x-admin-footer>
</body>

</html>
