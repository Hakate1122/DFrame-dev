<?php
if (!function_exists('getErrorPage')) {
  /**
   * Get error page content
   */
  function getErrorPage(int $code, string $message): string
  {
    return <<<HTML
  <!doctype html>
  <html lang="vi">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Error $code</title>

  <style>
  html, body {
    width: 100%;
    height: 100%;
    margin: 0;
  }

  body {
    background: #f6f7f9;
    font-family: Arial, Helvetica, sans-serif;
    color: #444;
  }

  .page {
    display: table;
    width: 100%;
    height: 100%;
  }

  .page-inner {
    display: table-cell;
    vertical-align: middle;
    text-align: center;
    padding: 16px;
  }

  .code {
    font-size: 20vw;
    line-height: 1;
    font-weight: bold;
    color: #e0e0e0;
  }

  .title {
    font-size: 5vw;
    margin: 8px 0;
  }

  @media (min-width: 768px) {
    .code { font-size: 140px; }
    .title { font-size: 32px; }
  }
  </style>
  </head>

  <body>
    <div class="page">
      <div class="page-inner">
        <div class="code">$code</div>
        <div class="title">$message</div>
      </div>
    </div>
  </body>
  </html>
  HTML;
  }
}

if (!function_exists('get404pages')) {
  /**
   * Get 404 page content
   */
  function get404pages($message = ''): string
  {
    return getErrorPage(404, trim($message) ?: 'Not Found');
  }
}

if (!function_exists('get403pages')) {
  /**
   * Get 403 page content
   */
  function get403pages(string $message = ''): string
  {
    return getErrorPage(403, trim($message) ?: 'Forbidden');
  }
}

if (!function_exists('get500pages')) {
  /**
   * Get 500 page content
   * @return bool|string
   */
  function get500pages($message = '')
  {
    return getErrorPage(500, trim($message) ?: 'Internal Server Error');
  }
}

if (!function_exists('get503pages')) {
  /**
   * Get 503 page content
   */
  function get503pages(string $message = ''): string
  {
    return getErrorPage(503, trim($message) ?: 'Service Unavailable');
  }
}
