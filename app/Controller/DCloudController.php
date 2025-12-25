<?php

namespace App\Controller;

class DCloudController extends Controller
{
    protected function getSourceDir(): string
    {
        return INDEX_DIR . 'source';
    }

    public function index()
    {
        jslog('DCloud loaded at directory: ' . $this->getSourceDir() .'');
        $this->render('DCloud.home');
    }

    public function listFiles()
    {
        $dir = $_GET['dir'] ?? '';
        $dir = ltrim(str_replace(['..', '\\'], '', $dir), '/');
        $base = $this->getSourceDir();
        $path = $base . ($dir !== '' ? '/' . $dir : '');

        if (!is_dir($path)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Directory not found', 'files' => []]);
            return;
        }

        $list = [];
        $items = scandir($path);
        foreach ($items as $it) {
            if ($it === '.' || $it === '..') continue;
            $full = $path . '/' . $it;
            $rel = ($dir !== '' ? ($dir . '/') : '') . $it;
            $list[] = [
                'name' => $it,
                'path' => $rel,
                'is_dir' => is_dir($full),
                'size' => is_dir($full) ? 0 : filesize($full),
                'modified' => filemtime($full),
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(['files' => $list]);
    }

    public function upload()
    {
        $dir = $_POST['dir'] ?? '';
        $dir = ltrim(str_replace(['..', '\\'], '', $dir), '/');
        $base = $this->getSourceDir();
        $targetDir = $base . ($dir !== '' ? '/' . $dir : '');

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (!isset($_FILES['file'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No file uploaded']);
            return;
        }

        $f = $_FILES['file'];
        $name = basename($f['name']);
        $dest = $targetDir . '/' . $name;

        if (move_uploaded_file($f['tmp_name'], $dest)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'file' => $name]);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
    }

    public function delete()
    {
        $file = $_POST['file'] ?? '';
        $file = ltrim(str_replace(['..', '\\'], '', $file), '/');
        $base = $this->getSourceDir();
        $full = $base . '/' . $file;

        if (!file_exists($full)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not found']);
            return;
        }

        if (is_dir($full)) {
            // only remove empty directories for safety
            $ok = @rmdir($full);
        } else {
            $ok = @unlink($full);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => (bool)$ok]);
    }

    public function rename()
    {
        $old = $_POST['old'] ?? '';
        $new = $_POST['new'] ?? '';
        $old = ltrim(str_replace(['..', '\\'], '', $old), '/');
        $new = ltrim(str_replace(['..', '\\'], '', $new), '/');
        $base = $this->getSourceDir();
        $oldp = $base . '/' . $old;
        $newp = $base . '/' . $new;

        if (!file_exists($oldp)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Source not found']);
            return;
        }

        $dir = dirname($newp);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ok = @rename($oldp, $newp);
        header('Content-Type: application/json');
        echo json_encode(['success' => (bool)$ok]);
    }
}
