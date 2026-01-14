(function() {
    var apiBase = 'dcloud/api';

    var sourceBase = /cloud/;
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