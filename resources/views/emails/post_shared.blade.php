<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Post Has Been Shared</title>
</head>

<body>
    <h1>Your Post Has Been Shared</h1>

    <p>Hello,</p>

    <p>Your post has been shared with another user. Here are the details:</p>

    <p>Post ID: {{ $share->post_id }}</p>
    <p>Share ID: {{ $share->id }}</p>
    <p>Shared with User ID: {{ $share->user_id }}</p>

    <p>Thank you!</p>
</body>

</html>