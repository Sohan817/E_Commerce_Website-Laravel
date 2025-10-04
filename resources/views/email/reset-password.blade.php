<!DOCTYPE html>
<html>

<head>
    <title>Reset password email</title>
</head>

<body style="font-family: Arial,Helvetica sans-serif;font-size:16px">
    <p>Hello, {{ $fromData['user']->name }}</p>
    <h1>You have Requested to change your password</h1>

    <p>Please click the given link to reset your password</p>

    <a href = "{{ route('front.resetPassword', $fromData['token']) }}">Click here</a>

    <p>Thank You</p>
</body>

</html>
