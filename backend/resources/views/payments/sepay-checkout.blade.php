<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to SePay</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background: rgba(15, 23, 42, 0.9);
            padding: 2rem;
            border-radius: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.35);
            max-width: 480px;
            text-align: center;
        }
        button {
            background: #2563eb;
            border: none;
            color: white;
            padding: 0.85rem 1.5rem;
            border-radius: 999px;
            font-size: 1rem;
            cursor: pointer;
        }
        button:disabled {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Đang chuyển tới SePay</h1>
        <p>Vui lòng đợi trong giây lát. Nếu trình duyệt không tự chuyển, bạn có thể nhấn nút bên dưới.</p>
        <form id="sepay-form" method="POST" action="{{ $endpoint }}">
            @foreach($payload as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <button type="submit">Thanh toán ngay</button>
        </form>
    </div>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            document.getElementById('sepay-form').submit()
        })
    </script>
</body>
</html>
