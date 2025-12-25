<?php
if (!function_exists('getErrorPage')) {
  /**
   * Get error page content
   * @param int $code
   * @param string $message
   * @return string
   */
  function getErrorPage(int $code, string $message): string
  {
    $html = <<<HTML
  <!doctype html>
  <html lang="vi">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Error $code</title>
    <style>
      html, body {
        height: 100%;
        margin: 0;
      }
      body {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: #777;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        font-size: clamp(12px, 5vw, 60px);
      }
    </style>
  </head>
  <body>
    $code - $message
  </body>
  </html>
  HTML;
    return $html;
  }
}

if (!function_exists('get404pages')) {
  /**
   * Get 404 page content
   * @return bool|string
   */
  function get404pages($message = ''): string
  {
    return getErrorPage(404, $message ?: 'Not Found');
  }
}

if (!function_exists('get403pages')) {
  /**
   * Get 403 page content
   * @return bool|string
   */
  function get403pages(string $message = ''): string
  {
    return getErrorPage(403, $message ?: 'Forbidden');
  }
}

if (!function_exists('get500pages')) {
  /**
   * Get 500 page content
   * @return bool|string
   */
  function get500pages($message = '')
  {
    return getErrorPage(500, $message ?: 'Internal Server Error');
  }
}

if (!function_exists('get503pages')) {
  /**
   * Get 503 page content
   * @return bool|string
   */
  function get503pages(string $message = ''): string
  {
    return getErrorPage(503, $message ?: 'Service Unavailable');
  }
}
