<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cloud Files Layout</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: #f4f6f8;
            color: #1f2937;
        }

        /* ================== LAYOUT ================== */
        .app {
            display: grid;
            /* Cột: Sidebar trái | Main | Sidebar phải */
            grid-template-columns: 240px 1fr 300px;
            /* Hàng: Header cố định | Nội dung co giãn */
            grid-template-rows: 56px 1fr;
            height: 100vh;
        }

        /* ================== HEADER ================== */
        .header {
            /* Header chiếm trọn chiều ngang (từ line 1 đến line cuối) */
            grid-column: 1 / -1;
            /* Grid row đã quy định height 56px, nhưng giữ lại cũng không sao */
            background: #1f6fb2;
            color: #fff;
            display: flex;
            align-items: center;
            padding: 0 16px;
        }

        .header .logo {
            font-weight: 600;
            margin-right: 16px;
        }

        .header .search {
            flex: 1;
        }

        .header input {
            width: 100%;
            max-width: 400px;
            padding: 6px 10px;
            border-radius: 6px;
            border: none;
            outline: none;
            /* Thêm để bỏ viền xanh khi click */
        }

        /* ================== SIDEBAR LEFT ================== */
        .sidebar-left {
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 12px;
        }

        .nav-item {
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 4px;
            transition: background 0.2s;
            /* Hiệu ứng mượt */
        }

        .nav-item.active,
        .nav-item:hover {
            background: #e6f0fa;
            color: #1f6fb2;
        }

        /* ================== MAIN ================== */
        .main {
            padding: 16px;
            overflow-y: auto;
            /* Chỉ cuộn nội dung phần Main */
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .btn {
            background: #1f6fb2;
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        /* ================== FILE LIST ================== */
        .table {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .row {
            /* Đã sửa: Thêm cột thứ 4 cho Actions */
            display: grid;
            grid-template-columns: 1fr 120px 120px 120px;
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            align-items: center;
        }

        .row.header {
            background: #f9fafb;
            font-weight: 600;
        }

        .row:last-child {
            border-bottom: none;
        }

        /* ================== SIDEBAR RIGHT ================== */
        .sidebar-right {
            background: #ffffff;
            border-left: 1px solid #e5e7eb;
            padding: 16px;
        }

        .panel-title {
            font-weight: 600;
            margin-bottom: 8px;
        }

        .panel {
            font-size: 14px;
            color: #4b5563;
        }

        /* ================== RESPONSIVE ================== */
        /* Tablet */
        @media (max-width: 1023px) {
            .app {
                grid-template-columns: 220px 1fr;
            }

            .sidebar-right {
                display: none;
            }

            /* Trên tablet, Actions vẫn giữ nguyên */
            .row {
                grid-template-columns: 1fr 90px 90px 120px;
                /* Điều chỉnh lại kích thước cột */
            }

            .row div:nth-child(4) {
                /* Cột Actions */
                display: flex;
            }
        }

        /* Mobile */
        @media (max-width: 767px) {
            .app {
                grid-template-columns: 1fr;
            }

            .sidebar-left {
                display: none;
            }

            .header {
                justify-content: space-between;
            }

            /* ĐÃ SỬA: Hiển thị 2 cột chính: Name và Actions */
            .row {
                grid-template-columns: 1fr 100px;
                /* Name (1fr) và Actions (100px) */
                padding-right: 4px;
            }

            /* ĐÃ SỬA: Ẩn cột Size (thứ 2) và Modified (thứ 3) */
            .row div:nth-child(2),
            /* Size */
            .row div:nth-child(3) {
                /* Modified */
                display: none;
            }

            /* ĐÃ SỬA: Đảm bảo cột Actions (thứ 4) luôn hiển thị trên mobile */
            .row div:nth-child(4) {
                display: flex !important;
                /* Dùng !important để đảm bảo không bị ghi đè */
                gap: 4px !important;
                /* Giảm khoảng cách giữa các nút */
            }

            /* Ẩn cột Modified (thứ 3) trong Header */
            .row.header div:nth-child(3) {
                display: none;
            }

            /* Header chỉ hiển thị 2 cột (Name, Size) - Size là vị trí của Actions */
            .row.header {
                grid-template-columns: 1fr 100px;
            }
        }

        /* Small mobile 320px */
        @media (max-width: 360px) {
            .header input {
                max-width: 200px;
            }

            /* Cần co lại thêm cho Small Mobile */
            .row {
                grid-template-columns: 1fr 80px;
            }

            .row div:nth-child(4) button {
                padding: 4px 6px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

    <div class="app">

        <div class="header">
            <div class="logo">☁ DCloud</div>
            <div class="search">
                <input type="text" placeholder="Search files..." />
            </div>
        </div>

        <aside class="sidebar-left">
            <div class="nav-item active"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="20" viewBox="0 0 20 22" style="vertical-align:middle;margin-right:6px;">
                    <rect x="2" y="5" width="16" height="12" rx="2" fill="#44aae1ff" />
                    <path d="M2 5a2 2 0 0 1 2-2h3.17a2 2 0 0 1 1.41.59l1.83 1.83H18a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2V5z" fill="#44aae1ff" />
                </svg>All files</div>
            <div class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" style="vertical-align:middle;margin-right:6px;">
                    <polygon points="10,2 12.59,7.36 18.51,8.09 14,12.26 15.18,18.09 10,15.13 4.82,18.09 6,12.26 1.49,8.09 7.41,7.36" fill="#fbbf24" />
                </svg>Favorites</div>
            <div class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="20" viewBox="0 0 20 22" style="vertical-align:middle;margin-right:6px;">
                    <rect x="4" y="4" width="12" height="12" rx="2" fill="#a78bfa" />
                    <rect x="6" y="6" width="8" height="2" rx="1" fill="#ede9fe" />
                    <rect x="6" y="10" width="8" height="2" rx="1" fill="#ede9fe" />
                    <rect x="6" y="14" width="5" height="2" rx="1" fill="#ede9fe" />
                </svg>Recent</div>
            <div class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="22" viewBox="0 0 20 22" style="vertical-align:middle;margin-right:6px;">
                    <rect x="5" y="7" width="10" height="8" rx="2" fill="#f87171" />
                    <rect x="8" y="10" width="1.5" height="4" rx="0.75" fill="#fff" />
                    <rect x="10.5" y="10" width="1.5" height="4" rx="0.75" fill="#fff" />
                    <rect x="3" y="5" width="14" height="2" rx="1" fill="#fca5a5" />
                </svg>Trash</div>
        </aside>

        <main class="main">
            <div class="toolbar" style="align-items:center;gap:12px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <button id="btnUp" class="btn" title="Up">↩︎ Up</button>
                    <div id="breadcrumb">/</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <label class="btn" for="fileInput" style="cursor:pointer;margin:0;">Upload</label>
                    <input id="fileInput" type="file" style="display:none;" />
                    <button id="refreshBtn" class="btn">Refresh</button>
                </div>
            </div>

            <div class="table" id="fileTable">
                <div class="row header">
                    <div>Name</div>
                    <div>Size</div>
                    <div>Modified</div>
                    <div>Actions</div>
                </div>
                <div id="fileList"></div>
            </div>
        </main>

        <aside class="sidebar-right">
            <div class="panel-title">Sharing</div>
            <div class="panel">
                Owner: Christine<br />
                Size: 1.6 MB<br />
                Last update: Sep 9
            </div>
        </aside>

    </div>

</body>

</html>
<script>
    (function() {
        var apiBase = 'dcloud/api';

        var sourceBase = '<?php echo str_replace("'", "\\'", source('')); ?>';
        var currentDir = '';

        var fileListEl = document.getElementById('fileList');
        var breadcrumbEl = document.getElementById('breadcrumb');
        var fileInput = document.getElementById('fileInput');

        function setBreadcrumb(dir) {
            breadcrumbEl.textContent = '/' + (dir || '');
        }

        function buildIcon(item) {
            // Giữ nguyên hàm gốc
            if (item.is_dir) {
                return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:8px"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h6a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" fill="#f59e0b"/></svg>';
            }

            var ext = (item.name.split('.').pop() || '').toLowerCase();
            var imageExt = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'];
            if (imageExt.indexOf(ext) !== -1) {
                return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:8px"><rect width="20" height="14" x="2" y="4" rx="2" fill="#10b981"/></svg>';
            }
            var codeExt = ['js', 'php', 'html', 'css', 'json', 'xml', 'sh'];
            if (codeExt.indexOf(ext) !== -1) {
                return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:8px"><rect width="18" height="14" x="3" y="5" rx="2" fill="#60a5fa"/></svg>';
            }
            if (ext === 'pdf') {
                return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:8px"><rect width="16" height="20" x="4" y="2" rx="2" fill="#ef4444"/></svg>';
            }
            if (['zip', 'rar', 'tar', 'gz'].indexOf(ext) !== -1) {
                return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:8px"><rect width="16" height="12" x="4" y="6" rx="2" fill="#a78bfa"/></svg>';
            }
            return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:8px"><rect width="16" height="12" x="4" y="6" rx="2" fill="#94a3b8"/></svg>';
        }

        function formatSize(s) {
            if (!s) return '';
            if (s < 1024) return s + ' B';
            if (s < 1024 * 1024) return Math.round(s / 1024) + ' KB';
            return (Math.round(s / (1024 * 1024) * 10) / 10) + ' MB';
        }

        function formatDate(ts) {
            if (!ts) return '';
            var d = new Date(ts * 1000);
            return d.toLocaleString();
        }

        function renderList(files) {
            // Giữ nguyên hàm gốc
            fileListEl.innerHTML = '';
            if (!files || !files.length) {
                fileListEl.innerHTML = '<div class="row"><div style="grid-column:1/-1;color:#6b7280;padding:12px">No files</div></div>';
                return;
            }

            files.forEach(function(item) {
                var row = document.createElement('div');
                row.className = 'row';

                var nameCell = document.createElement('div');
                nameCell.innerHTML = buildIcon(item) + '<span style="vertical-align:middle">' + escapeHtml(item.name) + '</span>';
                nameCell.style.cursor = 'pointer';
                nameCell.onclick = function() {
                    if (item.is_dir) {
                        currentDir = currentDir ? currentDir + '/' + item.name : item.name;
                        fetchList(currentDir);
                    } else {
                        var url = sourceBase + encodeURI(item.path);
                        window.open(url);
                    }
                };

                var sizeCell = document.createElement('div');
                sizeCell.textContent = item.is_dir ? '' : formatSize(item.size);

                var modCell = document.createElement('div');
                modCell.textContent = formatDate(item.modified);

                var actionCell = document.createElement('div');
                actionCell.style.display = 'flex';
                actionCell.style.gap = '8px';
                actionCell.style.justifyContent = 'flex-end';

                var del = document.createElement('button');
                del.className = 'btn';
                del.style.padding = '4px 8px';
                del.textContent = 'Delete';
                del.onclick = function(e) {
                    e.stopPropagation();
                    if (!confirm('Delete "' + item.name + '"?')) return;
                    post(apiBase + '/delete', {
                        file: item.path
                    }).then(function() {
                        fetchList(currentDir);
                    });
                };

                var rnm = document.createElement('button');
                rnm.className = 'btn';
                rnm.style.padding = '4px 8px';
                rnm.textContent = 'Rename';
                rnm.onclick = function(e) {
                    e.stopPropagation();
                    var name = prompt('New name', item.name);
                    if (!name) return;
                    post(apiBase + '/rename', {
                        old: item.path,
                        new: (currentDir ? currentDir + '/' : '') + name
                    }).then(function() {
                        fetchList(currentDir);
                    });
                };

                actionCell.appendChild(rnm);
                actionCell.appendChild(del);

                row.appendChild(nameCell);
                row.appendChild(sizeCell);
                row.appendChild(modCell);
                row.appendChild(actionCell);

                fileListEl.appendChild(row);
            });
        }

        function escapeHtml(s) {
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        // Hàm post sử dụng XMLHttpRequest
        function post(url, data) {
            return new Promise(function(resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', url, true);

                var fd = new FormData();
                for (var k in data) {
                    if (Object.prototype.hasOwnProperty.call(data, k)) {
                        fd.append(k, data[k]);
                    }
                }

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        var ct = xhr.getResponseHeader('content-type') || '';
                        if (ct.indexOf('application/json') !== -1) {
                            try {
                                resolve(JSON.parse(xhr.responseText));
                            } catch (e) {
                                reject(e);
                            }
                        } else {
                            resolve({
                                error: xhr.responseText,
                                status: xhr.status
                            });
                        }
                    } else {
                        reject(new Error('Status: ' + xhr.status));
                    }
                };

                xhr.onerror = function() {
                    reject(new Error('Network error'));
                };

                xhr.send(fd);
            });
        }

        // Hàm fetchList sử dụng XMLHttpRequest
        function fetchList(dir) {
            var u = apiBase + '/list' + (dir ? '?dir=' + encodeURIComponent(dir) : '');
            var xhr = new XMLHttpRequest();
            xhr.open('GET', u, true);

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    var ct = xhr.getResponseHeader('content-type') || '';
                    if (ct.indexOf('application/json') === -1) {
                        console.error('Non-JSON response from', u, xhr.status, xhr.responseText);
                        fileListEl.innerHTML = '<div class="row"><div style="grid-column:1/-1;color:#ef4444;padding:12px">Error: ' + escapeHtml(String(xhr.status)) + '</div></div>';
                        return;
                    }
                    try {
                        var json = JSON.parse(xhr.responseText);
                        if (json) {
                            var files = json.files || [];
                            setBreadcrumb(dir);
                            renderList(files);
                        }
                    } catch (e) {
                        console.error(e);
                        fileListEl.innerHTML = '<div class="row"><div style="grid-column:1/-1;color:#ef4444;padding:12px">Error parsing response</div></div>';
                    }
                } else {
                    fileListEl.innerHTML = '<div class="row"><div style="grid-column:1/-1;color:#ef4444;padding:12px">Error: ' + escapeHtml(String(xhr.status)) + '</div></div>';
                }
            };

            xhr.onerror = function() {
                console.error('Network error');
                fileListEl.innerHTML = '<div class="row"><div style="grid-column:1/-1;color:#ef4444;padding:12px">Error fetching files</div></div>';
            };

            xhr.send();
        }

        document.getElementById('refreshBtn').addEventListener('click', function() {
            fetchList(currentDir);
        });

        document.getElementById('btnUp').addEventListener('click', function() {
            if (!currentDir) return;
            var parts = currentDir.split('/');
            parts.pop();
            currentDir = parts.join('/');
            fetchList(currentDir);
        });

        fileInput.addEventListener('change', function() {
            var f = this.files[0];
            if (!f) return;
            var fd = new FormData();
            fd.append('file', f);
            fd.append('dir', currentDir);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', apiBase + '/upload', true);

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        var j = JSON.parse(xhr.responseText);
                        if (j.success) {
                            fetchList(currentDir);
                        } else {
                            alert('Upload failed');
                        }
                    } catch (e) {
                        alert('Upload error');
                    }
                } else {
                    alert('Upload failed: ' + xhr.status);
                }
            };

            xhr.onerror = function() {
                alert('Upload error');
            };

            xhr.send(fd);

            try {
                fileInput.value = '';
            } catch (e) {}
        });

        fetchList('');
    })();
</script>