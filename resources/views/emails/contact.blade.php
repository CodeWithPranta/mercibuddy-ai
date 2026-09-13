<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Form Submission</title>
</head>
<body>
    <h2>Contact Form Submission</h2>

    <p><strong>From:</strong> {{ $formData['email'] }}</p>
    <p><strong>Subject:</strong> {{ $formData['subject'] }}</p>

    <hr>

    <p><strong>Message:</strong></p>
    <p>{{ $formData['message'] }}</p>
</body>
</html>
