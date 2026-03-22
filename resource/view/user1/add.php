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
        <div style="color: red;">
            <?php if (is_array($error)): ?>
                <?php foreach ($error as $field => $messages): ?>
                    <?php if (is_array($messages)): ?>
                        <?php foreach ($messages as $msg): ?>
                            <p><?= htmlspecialchars((string)$msg) ?></p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p><?= htmlspecialchars((string)$messages) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?= htmlspecialchars((string)$error) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <form action="<?= route('user.store') ?>" method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?=old('name')?>">
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?=old('email')?>">
        <br>
        <button type="submit">Add User</button>
    </form>
</body>
</html>