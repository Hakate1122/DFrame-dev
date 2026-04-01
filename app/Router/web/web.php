<?php

use App\Middleware\UserAuthencation;
use DFrame\Application\Log;
use DFrame\Application\View;
use DFrame\Application\Mail as Gmail;

UserAuthencation::sign();

$router = new DFrame\Application\Router();

$router->sign('GET /', [\App\Controller\HomeController::class, 'home'])->name('home');

$router->sign('GET /h', function () {
    echo "<img src='" . asset('unnamed.jpg') . "' alt='Logo'>";
})->name('home.alias');

$router->sign('GET /test',function(){
    $q = $_GET['q'] ?? '';
    return "You searched for: " . $q;
});

$router->sign('GET /morse', function () {
    return View::render('morse');
})->name('morse');

$router->sign('GET /ws/chat', function () {
    return View::render('ws/chat');
})->name('ws.chat');

$router->sign('GET|POST /mail', function () {

    $mail = new Gmail([
        'username' => 'datahihi1100@gmail.com',
        'password' => 'iead lols hpgi dova',
        'from' => '',
        'fromname' => 'DFrame Mailer',
    ]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $to = $_POST['to'];
        $cc = $_POST['cc'] ?? '';
        $bcc = $_POST['bcc'] ?? '';
        $subject = $_POST['subject'];
        $body = $_POST['body'];
        $log = new Log();
        $log->info('Incoming _FILES', ['files' => $_FILES]);
        $file = $_FILES['attachment'] ?? null;
        $movedAttachmentPath = null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            if (is_uploaded_file($file['tmp_name'])) {
                $tmpDir = sys_get_temp_dir();
                $destPath = $tmpDir . DIRECTORY_SEPARATOR . uniqid('upload_', true) . '_' . basename($file['name']);

                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $movedAttachmentPath = $destPath;
                    $mail->addAttachment($movedAttachmentPath, $file['name']);
                } else {
                    $log = new Log();
                    $log->error('Failed to move uploaded file to temp dir.', ['tmp' => $file['tmp_name'], 'dest' => $destPath]);
                }
            } else {
                $log = new Log();
                $log->warning('Uploaded file not recognized as uploaded file.', ['tmp' => $file['tmp_name']]);
            }
        } else {
            $log = new Log();
            $log->warning('No attachment uploaded or there was an upload error.', ['file_error' => $file['error'] ?? 'No file']);
        }

        try {
            $mail->to($to)
                ->cc($cc)
                ->bcc($bcc)
                ->subject($subject)
                ->html($body)
                ->send();

            if ($movedAttachmentPath && file_exists($movedAttachmentPath)) {
                @unlink($movedAttachmentPath);
            }

            flash('success', 'Email sent successfully!');
        } catch (\RuntimeException $e) {
            throw $e;
        }
    }
    // css
    echo '<style>
    form {
        max-width: 400px;
        margin: 20px auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    input, textarea {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 3px;
    }
    button {
        padding: 10px 20px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
    button:hover {
        background-color: #218838;
    }
    </style>';
    // html
    echo '<h2 style="text-align:center;">' . getFlash('success') . '</h2>';
    echo '<form method="POST" action="' . route('mail') . '" enctype="multipart/form-data">
    <input type="email" name="to" placeholder="Recipient Email" required>
    <input type="text" name="cc" placeholder="CC Email (optional)">
    <input type="text" name="bcc" placeholder="BCC Email (optional)">
    <input type="text" name="subject" placeholder="Subject" required>
    <textarea name="body" placeholder="Email Body" required></textarea>
    <input type="file" name="attachment">
    <button type="submit">Send Email</button>
</form>';
})->name('mail');

$router->sign('GET /sitemap.xml', [\App\Controller\SitemapController::class, 'index'])->name('sitemap');

$router->scanControllerAttributes([
    App\Controller\UserController::class,
]);
