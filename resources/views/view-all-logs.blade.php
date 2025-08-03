<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Logs</title>
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
</head>

<body>
    <x-admin-nav></x-admin-nav>
    <div class="container-fluid mt-4 mb-4 lexend">
        <div class="col-lg-12 d-flex flex-column justify-content-between"
            style="background-color: white; margin-right:0px; border-radius: 30px; box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2); padding:10px; min-height: 85vh;">
            <h1 class="fw-bold mt-3" style="margin-left: 1vw">Activity Logs</h1>
            <hr>
            <div class="table-responsive" style="flex: 1 1 auto; overflow-y: auto;">
                <table class="table table-log">
                    <thead>
                        <tr>
                            <th scope="col-1-lg">No. </th>
                            <th scope="col-1-lg">Username</th>
                            <th scope="col-2-lg">ID</th>
                            <th scope="col-1-lg">Role</th>
                            <th scope="col-1-lg">URL</th>
                            <th scope="col-1-lg">Method</th>
                            <th scope="col-2-lg">Description</th>
                            <th scope="col-1-lg">IP</th>
                            <th scope="col-2-lg">Time</th>

                        </tr>
                    </thead>
                    <tbody>
                        @if ($all_logs->count() == 0)
                            <div class="text-center mt-5">
                                <h4>No Logs Available.</h4>
                            </div>

                            <h1>No Data</h1>
                        @else
                            @foreach ($all_logs as $log)
                                <tr>
                                    <th scope="row">
                                        {{ ($all_logs->currentPage() - 1) * $all_logs->perPage() + $loop->iteration }}
                                    </th>
                                    <td>{{ $log->name }}</td>
                                    <td>{{ $log->userId }}</td>
                                    <td>{{ $log->role }}</td>
                                    <td>{{ $log->url }}</td>
                                    <td>{{ $log->method }}</td>
                                    <td>{{ $log->description }}</td>
                                    <td>{{ $log->ip_address }}</td>
                                    <td>{{ $log->accessed_at }}</td>
                                    {{-- <td><a type="button" class="btn btn-info fs-6 text-center p-1" style="height: 30px; width:50px">Detail</a></td> --}}
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            @if ($all_logs->lastPage() > 1)
                <div class="d-flex justify-content-center mt-3">
                    <nav aria-label="Activity log pagination">
                        <ul class="pagination catering-pagination flex-wrap justify-content-center">
                            {{-- Previous Page Link --}}
                            <li class="page-item {{ $all_logs->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $all_logs->previousPageUrl() ?? '#' }}"
                                    tabindex="-1">&laquo;</a>
                            </li>

                            {{-- Pagination Elements --}}
                            @for ($i = 1; $i <= $all_logs->lastPage(); $i++)
                                <li class="page-item {{ $all_logs->currentPage() == $i ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $all_logs->url($i) }}">{{ $i }}</a>
                                </li>
                            @endfor

                            {{-- Next Page Link --}}
                            <li class="page-item {{ !$all_logs->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $all_logs->nextPageUrl() ?? '#' }}">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            @endif

        </div>
    </div>

    <x-footer-admin></x-footer-admin>

</body>

</html>
