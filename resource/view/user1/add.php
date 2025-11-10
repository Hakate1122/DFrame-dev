<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
</head>
<body>
    <h1>Add User</h1>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form action="<?= route('user.store') ?>" method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name">
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email">
        <br>
        <button type="submit">Add User</button>
    </form>
</body>
</html>