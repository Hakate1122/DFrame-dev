<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Cloud Files Layout</title>

<style>
/* ================== RESET ================== */
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
    outline: none; /* Thêm để bỏ viền xanh khi click */
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
    transition: background 0.2s; /* Hiệu ứng mượt */
}

.nav-item.active,
.nav-item:hover {
    background: #e6f0fa;
    color: #1f6fb2;
}

/* ================== MAIN ================== */
.main {
    padding: 16px;
    overflow-y: auto; /* Chỉ cuộn nội dung phần Main */
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
    display: grid;
    grid-template-columns: 1fr 120px 120px;
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
}

/* Mobile */
@media (max-width: 767px) {
    .app {
        grid-template-columns: 1fr;
        /* Trên mobile header vẫn giữ nguyên, grid rows vẫn hoạt động tốt */
    }

    .sidebar-left {
        display: none;
    }

    .header {
        justify-content: space-between;
    }
    
    /* Ẩn search bar trên mobile nếu cần thiết */
    /* .header .search { display: none; } */

    .row {
        grid-template-columns: 1fr 80px;
    }

    .row div:nth-child(3) {
        display: none;
    }
}

/* Small mobile 320px */
@media (max-width: 360px) {
    .header input {
        max-width: 200px;
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
        <div class="nav-item active"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="20" viewBox="0 0 20 22" style="vertical-align:middle;margin-right:6px;"><rect x="2" y="5" width="16" height="12" rx="2" fill="#44aae1ff"/><path d="M2 5a2 2 0 0 1 2-2h3.17a2 2 0 0 1 1.41.59l1.83 1.83H18a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2V5z" fill="#44aae1ff"/></svg>All files</div>
        <div class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" style="vertical-align:middle;margin-right:6px;"><polygon points="10,2 12.59,7.36 18.51,8.09 14,12.26 15.18,18.09 10,15.13 4.82,18.09 6,12.26 1.49,8.09 7.41,7.36" fill="#fbbf24"/></svg>Favorites</div>
        <div class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="20" viewBox="0 0 20 22" style="vertical-align:middle;margin-right:6px;"><rect x="4" y="4" width="12" height="12" rx="2" fill="#a78bfa"/><rect x="6" y="6" width="8" height="2" rx="1" fill="#ede9fe"/><rect x="6" y="10" width="8" height="2" rx="1" fill="#ede9fe"/><rect x="6" y="14" width="5" height="2" rx="1" fill="#ede9fe"/></svg>Recent</div>
        <div class="nav-item"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="22" viewBox="0 0 20 22" style="vertical-align:middle;margin-right:6px;"><rect x="5" y="7" width="10" height="8" rx="2" fill="#f87171"/><rect x="8" y="10" width="1.5" height="4" rx="0.75" fill="#fff"/><rect x="10.5" y="10" width="1.5" height="4" rx="0.75" fill="#fff"/><rect x="3" y="5" width="14" height="2" rx="1" fill="#fca5a5"/></svg>Trash</div>
    </aside>

    <main class="main">
        <div class="toolbar">
            <h2>All files</h2>
            <button class="btn">+ New</button>
        </div>

        <div class="table">
            <div class="row header">
                <div>Name</div>
                <div>Size</div>
                <div>Modified</div>
            </div>


            <div class="row">
                <div><svg xmlns="http://www.w3.org/2000/svg" width="18" height="20" viewBox="0 0 20 22" style="vertical-align:middle;margin-right:6px;"><rect x="2" y="5" width="16" height="12" rx="2" fill="#44aae1ff"/><path d="M2 5a2 2 0 0 1 2-2h3.17a2 2 0 0 1 1.41.59l1.83 1.83H18a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2V5z" fill="#44aae1ff"/></svg>Assets</div>
                <div>12.6 MB</div>
                <div>Sep 15</div>
            </div>

            <div class="row">
                <div><svg xmlns="http://www.w3.org/2000/svg" width="18" height="20" viewBox="0 0 20 22" style="vertical-align:middle;margin-right:6px;"><rect x="2" y="5" width="16" height="12" rx="2" fill="#44aae1ff"/><path d="M2 5a2 2 0 0 1 2-2h3.17a2 2 0 0 1 1.41.59l1.83 1.83H18a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2V5z" fill="#44aae1ff"/></svg>Artwork</div>
                <div>46.5 MB</div>
                <div>Sep 9</div>
            </div>

            <div class="row">
                <div><svg xmlns="http://www.w3.org/2000/svg" width="18" height="20" viewBox="0 0 20 22" style="vertical-align:middle;margin-right:6px;"><rect x="2" y="5" width="16" height="12" rx="2" fill="#44aae1ff"/><path d="M2 5a2 2 0 0 1 2-2h3.17a2 2 0 0 1 1.41.59l1.83 1.83H18a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2V5z" fill="#44aae1ff"/></svg>Event Photos</div>
                <div>1.6 MB</div>
                <div>Sep 9</div>
            </div>

            <div class="row">
                <div><svg xmlns="http://www.w3.org/2000/svg" width="18" height="20" viewBox="0 0 20 22" style="vertical-align:middle;margin-right:6px;"><rect x="3" y="3" width="14" height="16" rx="2" fill="#a78bfa"/><rect x="6" y="6" width="8" height="2" rx="1" fill="#ede9fe"/><rect x="6" y="10" width="8" height="2" rx="1" fill="#ede9fe"/><rect x="6" y="14" width="5" height="2" rx="1" fill="#ede9fe"/></svg>Readme.md</div>
                <div>2 KB</div>
                <div>Today</div>
            </div>
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