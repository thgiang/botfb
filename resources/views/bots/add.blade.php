<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <style>
        ::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
            color: #CFCFCF !important;
            opacity: 1; /* Firefox */
        }

        :-ms-input-placeholder { /* Internet Explorer 10-11 */
            color: #CFCFCF !important;
        }

        ::-ms-input-placeholder { /* Microsoft Edge */
            color: #CFCFCF !important;
        }
    </style>
    <title>Bot tương tác</title>
</head>
<body>
<div id="app">
    <bots-add-component></bots-add-component>
</div>

{{--<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"--}}
{{--        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"--}}
{{--        crossorigin="anonymous"></script>--}}
{{--<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"--}}
{{--        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"--}}
{{--        crossorigin="anonymous"></script>--}}
{{--<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"--}}
{{--        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"--}}
{{--        crossorigin="anonymous"></script>--}}
<script src="{{ mix('js/app.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
</body>
</html>