<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
          integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <title>Bot cảm xúc</title>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12 mt-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title text-center">Danh sách chức năng</h3>
                </div>
                <div class="card-body">
                    <div><a role="button" class="btn btn-warning" href="/add" target="_blank">Thêm bot</a>
                        <a role="button" class="btn btn-success" href="/api/bots" target="_blank">Danh sách bot đã
                            thêm</a>
                        <a role="button" class="btn btn-info" href="/api/bots/logs" target="_blank">Lịch sử hoạt động
                            của bots đã
                            thêm</a></div>
                    <div class="mt-1">
                        <p class="card-text">Xóa bot: https://codedao.jas.plus/api/bots/delete?id=XXX (XXX là ID của bot
                            muốn xóa)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
