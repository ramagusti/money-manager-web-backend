<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
        }

        .email-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
        }

        .email-button {
            background: #eab308;
            color: white;
            padding: 12px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="email-container">
        @yield('content')
    </div>
</body>

</html>