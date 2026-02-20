<?php
function formatBytes($bytes)
{
    return round($bytes / 1024 / 1024, 2) . ' MB';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f4f4f4;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            color: #333;
        }

        .wrapper {
            width: 960px;
            margin: 30px auto;
            background: #fff;
            border: 1px solid #ddd;
        }

        @media screen and (max-width: 980px) {
            .wrapper {
                width: auto;
                margin: 10px;
            }
        }

        @media screen and (max-width: 600px) {
            body {
                font-size: 13px;
            }

            .header {
                font-size: 18px;
                padding: 15px;
            }

            .content {
                padding: 15px;
            }
        }

        @media screen and (max-width: 320px) {
            body {
                font-size: 12px;
            }

            .content {
                padding: 10px;
            }
        }

        @media screen and (max-width: 200px) {
            body {
                font-size: 11px;
            }

            .header,
            .content,
            .footer {
                padding: 5px;
            }
        }

        .header {
            background: #2c3e50;
            color: #fff;
            padding: 20px;
            font-size: 22px;
        }

        .content {
            padding: 20px;
        }

        h1 {
            margin-top: 0;
            font-size: 24px;
        }

        h3 {
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        ul li {
            padding: 6px 0;
            border-bottom: 1px dotted #ccc;
            word-wrap: break-word;
        }

        code {
            background: #eee;
            padding: 2px 4px;
            font-family: Consolas, monospace;
            font-size: 13px;
            word-wrap: break-word;
        }

        .footer {
            background: #fafafa;
            padding: 10px 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #777;
        }
    </style>

</head>

<body>
    <div class="wrapper">
        <div class="header">
            DFrame Framework
        </div>

        <div class="content">
            <h1>Congratulations! DFrame Framework has started successfully.</h1>

            <h3>Server Information</h3>

            <ul>
                <li>PHP Version: <?= $phpVersion ?></li>
                <li>DFrame Version: <?= $dframeVersion ?></li>
                <li>Operating System: <?= $os ?></li>
                <li>Web Server: <?= $server ?></li>
                <li>Memory Usage: <?= formatBytes($memory) ?> / <?= $memoryLimit ?></li>
            </ul>

            <p>
                Edit <code>resource/view/home.php</code> to change this page.
            </p>

            <p>
                Controller: <code>app/Controller/HomeController.php</code><br>
            </p>
            <p>
                Route: <code>app/Route/web/web.php</code>
            </p>
        </div>

        <div class="footer">
            DFrame &copy; 2025 - <?= date('Y') ?>
        </div>
    </div>
</body>

</html>