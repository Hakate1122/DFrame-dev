<?php

namespace DFrame\Application;

use Exception;

/**
 * **File Storage Handler**
 * 
 * File class for handling file storage operations with support for local and FTP drivers.
 */
class File
{
    protected string $driver;        // local | ftp
    protected string $root;

    protected $ftp;                  // connection

    public function __construct(array $config)
    {
        $this->driver = $config['driver'] ?? 'local';
        $this->root = rtrim($config['root'] ?? '/', '/');

        if ($this->driver === 'ftp') {
            $this->connectFTP($config);
        }
    }

    /* ----- COMMON UTIL ----- */
    protected function fullPath(string $path): string
    {
        return $this->root . '/' . ltrim($path, '/');
    }

    public function exists(string $path): bool
    {
        if ($this->driver === 'local') {
            return file_exists($this->fullPath($path));
        }

        return ftp_size($this->ftp, $this->fullPath($path)) !== -1;
    }

    public function delete(string $path): bool
    {
        if ($this->driver === 'local') {
            return @unlink($this->fullPath($path));
        }

        return ftp_delete($this->ftp, $this->fullPath($path));
    }

    public function url(string $path): string
    {
        return $this->fullPath($path);
    }

    /* ----- READ / WRITE ----- */
    public function read(string $path): string
    {
        if ($this->driver === 'local') {
            return file_get_contents($this->fullPath($path));
        }

        $temp = fopen('php://temp', 'r+');
        ftp_fget($this->ftp, $temp, $this->fullPath($path), FTP_BINARY);
        rewind($temp);

        return stream_get_contents($temp);
    }

    public function write(string $path, string $content): bool
    {
        if ($this->driver === 'local') {
            $this->ensureDir(dirname($this->fullPath($path)));
            return file_put_contents($this->fullPath($path), $content) !== false;
        }

        return $this->ftpPutContent($this->fullPath($path), $content);
    }

    public function append(string $path, string $content): bool
    {
        $old = $this->exists($path) ? $this->read($path) : '';
        return $this->write($path, $old . $content);
    }

    /* ----- LOCAL HELPER ----- */
    protected function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    /* ----- FTP ZONE ----- */
    protected function connectFTP(array $config): void
    {
        $this->ftp = ftp_connect(
            $config['host'],
            $config['port'] ?? 21
        );

        if (!$this->ftp) {
            throw new Exception("Cannot connect FTP");
        }

        if (!ftp_login($this->ftp, $config['username'], $config['password'])) {
            throw new Exception("FTP login failed");
        }

        ftp_pasv($this->ftp, $config['passive'] ?? true);
    }

    protected function ftpPutContent(string $remotePath, string $content): bool
    {
        $temp = fopen('php://temp', 'r+');
        fwrite($temp, $content);
        rewind($temp);

        return ftp_fput($this->ftp, $remotePath, $temp, FTP_BINARY);
    }

    public function __destruct()
    {
        if ($this->driver === 'ftp' && $this->ftp) {
            ftp_close($this->ftp);
        }
    }
}
