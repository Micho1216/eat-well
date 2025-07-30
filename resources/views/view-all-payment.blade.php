<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>View All Payment</title>
    <!-- Bootstrap CSS (biasanya sudah ada) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>
    <x-admin-nav></x-admin-nav>

    <div class="container-fluid">

        <h1 class="lexend fw-bold text-center mt-5 mb-5">{{ __('view-all-payment.title') }}</h1>


        {{-- <h6 class="fw-bold lexend">{{ __('view-all-payment.add_payment') }}</h6> --}}
        <div class="row lexend">

            {{-- <form action="{{ route('add-new-payment') }}" method="POST" class="d-flex">
                @csrf
                @method('POST')

                <input class="form-control mr-3" type="text" placeholder="Payment Method" name="paymentMethod"
                    id="paymentMethod" class="col-8" style="margin-right:2vh" required>
                <button class="btn btn-outline-success" type="submit">{{ __('view-all-payment.add_button') }}</button>

            </form> --}}
        </div>

        <hr>

        <div class="" style="color: red;">
                {{ session()->get('message_del', '') }}
        </div>

        <div class="" style="color: rgb(15, 157, 24);">
            {{ session()->get('message_add', '') }}
        </div>

        @if ($payments->isEmpty())
            <h3 class="text-center fw-bold lexend mt-5" style="margin-bottom: 130px">{{ __('view-all-payment.no_data') }}</h3>
        @else
        <table class="table text-center" style="margin-bottom: 130px">
            <thead>
                <tr>
                    <th scope="col-4" style="background-color: rgb(165, 203, 165) !important">ID</th>
                    <th scope="col-4" style="background-color: rgb(165, 203, 165) !important">{{ __('view-all-payment.table_method') }}</th>
                    <th scope="col-4" style="background-color: rgb(165, 203, 165) !important"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $payment)
                    <tr>
                        {{-- <td>{{ $payment->methodId }}</td> --}}
                        <th scope="row">{{ $loop->iteration }}</th>
                        {{-- <th scope="row">{{ $loop->iteration }}</th> --}}
                        <td>{{ $payment->name }}</td>
                        <td>
                            <form action="{{ route('delete-payment', ['id' => $payment->methodId]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">{{ __('view-all-payment.delete') }}</button>
                            </form>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>

        @endif

    </div>




    <x-admin-footer></x-admin-footer>
</body>

</html>
