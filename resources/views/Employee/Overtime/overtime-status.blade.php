<!DOCTYPE html>
<html>

<head>
    <title>Leave Application Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f7f9fc;
        }

        .email-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background-color: #2c3e50;
            color: #fff;
            text-align: center;
            padding: 15px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .email-body {
            padding: 20px;
        }

        .email-body p {
            margin: 10px 0;
            line-height: 1.6;
        }

        .email-body ul {
            list-style-type: none;
            padding: 0;
            margin: 10px 0;
        }

        .email-body ul li {
            margin: 8px 0;
        }

        .email-body ul li strong {
            color: #2c3e50;
        }

        .email-footer {
            text-align: center;
            font-size: 12px;
            color: #888;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #27ae60;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #218c53;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Overtime Application Status</h1>
        </div>
        <div class="email-body">
            <p>Hi, {{ $overtime->employee->first_name }}</p>
            <p>Your leave application has been <strong>{{ $status }}</strong>.</p>

            <p><strong>Submission Details:</strong></p>
            <ul>
                <li><strong>Date:</strong> {{ $overtime->overtime_date }}</li>
                <li><strong>Duration:</strong> {{ $overtime->duration }} hour(s)</li>
                <li><strong>Notes</strong> {{ $overtime->notes }}</li>
            </ul>

            <p>Thank you,<br>HR Department</p>
        </div>
        <div class="email-footer">
            &copy; {{ date('Y') }} HR Department. All Rights Reserved.
        </div>
    </div>
</body>

</html>
