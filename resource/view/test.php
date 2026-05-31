<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern iGalerie Layout</title>
    <!-- Sử dụng Font Google hiện đại (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icon từ Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        /* =========================================
           1. BIẾN MÀU SẮC & CÀI ĐẶT CƠ BẢN
        ========================================= */
        :root {
            --primary-color: #3b82f6; /* Xanh dương hiện đại */
            --bg-color: #f8fafc; /* Nền xám nhạt rất dịu mắt */
            --surface-color: #ffffff; /* Nền của các thẻ (card) */
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-radius: 12px;
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-radius;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* =========================================
           2. HEADER & NAVIGATION (Sử dụng Flexbox)
        ========================================= */
        header {
            background-color: var(--surface-color);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            font-weight: 500;
            color: var(--text-muted);
            transition: color var(--transition-speed);
        }

        .nav-links a:hover, .nav-links a.active {
            color: var(--primary-color);
        }

        /* =========================================
           3. PHẦN MAIN CONTENT & TOOLBAR
        ========================================= */
        main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        /* Breadcrumb (Đường dẫn trang) */
        .breadcrumb {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .breadcrumb i { font-size: 1.1rem; }

        /* Toolbar: Tiêu đề và nút Lọc */
        .gallery-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 2rem;
        }

        .gallery-title h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .gallery-title p {
            color: var(--text-muted);
        }

        .filter-group button {
            padding: 0.5rem 1rem;
            background: var(--surface-color);
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-muted);
            transition: all var(--transition-speed);
            margin-left: 0.5rem;
        }

        .filter-group button:hover, .filter-group button.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* =========================================
           4. GALLERY GRID (Sử dụng CSS Grid)
        ========================================= */
        .gallery-grid {
            display: grid;
            /* Phép màu của CSS Grid: Tự động tính toán số cột sao cho mỗi cột tối thiểu 280px */
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        /* Thẻ chứa ảnh (Card) */
        .gallery-item {
            background: var(--surface-color);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
            position: relative;
            cursor: pointer;
        }

        .gallery-item:hover {
            transform: translateY(-5px); /* Nổi lên khi hover */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* Khung chứa ảnh để xử lý tỉ lệ (Aspect Ratio) */
        .img-container {
            width: 100%;
            height: 220px;
            overflow: hidden;
            position: relative;
        }

        .img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Đảm bảo ảnh không bị méo */
            transition: transform 0.5s ease;
        }

        .gallery-item:hover .img-container img {
            transform: scale(1.08); /* Zoom nhẹ ảnh khi hover */
        }

        /* Lớp phủ (Overlay) hiện lên khi hover */
        .overlay {
            position: absolute;
            inset: 0; /* Tương đương top:0; right:0; bottom:0; left:0; */
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity var(--transition-speed);
        }

        .gallery-item:hover .overlay {
            opacity: 1;
        }

        .overlay i {
            color: white;
            font-size: 3rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            padding: 10px;
            backdrop-filter: blur(4px); /* Hiệu ứng kính mờ */
        }

        /* Thông tin bên dưới ảnh */
        .item-info {
            padding: 1rem;
        }

        .item-info h3 {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
            color: var(--text-main);
        }

        .item-info p {
            font-size: 0.85rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* =========================================
           5. RESPONSIVE DESIGN (Màn hình nhỏ)
        ========================================= */
        @media (max-width: 768px) {
            .gallery-toolbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .filter-group button {
                margin-left: 0;
                margin-right: 0.5rem;
                margin-bottom: 0.5rem;
            }
            .nav-links {
                display: none; /* Ẩn menu trên mobile (có thể làm menu hamburger sau) */
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="nav-container">
            <div class="logo">
                <i class='bx bx-image-alt'></i> iGalerie Modern
            </div>
            <ul class="nav-links">
                <li><a href="#" class="active">Trang chủ</a></li>
                <li><a href="#">Album</a></li>
                <li><a href="#">Thể loại</a></li>
                <li><a href="#">Giới thiệu</a></li>
            </ul>
        </div>
    </header>

    <!-- Nội dung chính -->
    <main>
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="#"><i class='bx bx-home' ></i> Trang chủ</a> 
            <i class='bx bx-chevron-right'></i> 
            <span>Album Thiên nhiên 2026</span>
        </div>

        <!-- Toolbar (Tiêu đề & Bộ lọc) -->
        <div class="gallery-toolbar">
            <div class="gallery-title">
                <h1>Thiên nhiên kỳ thú</h1>
                <p>Khám phá 124 bức ảnh tuyệt đẹp được chụp trong năm nay.</p>
            </div>
            <div class="filter-group" id="filter-container">
                <button class="active" data-filter="all">Tất cả</button>
                <button data-filter="landscape">Phong cảnh</button>
                <button data-filter="macro">Cận cảnh</button>
            </div>
        </div>

        <!-- Lưới Hình Ảnh -->
        <div class="gallery-grid" id="gallery">
            <!-- Item 1 -->
            <div class="gallery-item" data-category="landscape">
                <div class="img-container">
                    <img src="https://images.unsplash.com/photo-1472214103451-9374bd1c798e?auto=format&fit=crop&w=600&q=80" alt="Phong cảnh núi">
                    <div class="overlay">
                        <i class='bx bx-search-alt-2'></i>
                    </div>
                </div>
                <div class="item-info">
                    <h3>Núi rừng hoàng hôn</h3>
                    <p><i class='bx bx-calendar'></i> 12/05/2026 • 2.4 MB</p>
                </div>
            </div>

            <!-- Item 2 -->
            <div class="gallery-item" data-category="landscape">
                <div class="img-container">
                    <img src="https://images.unsplash.com/photo-1506744012022-2ff21316242d?auto=format&fit=crop&w=600&q=80" alt="Sông suối">
                    <div class="overlay">
                        <i class='bx bx-search-alt-2'></i>
                    </div>
                </div>
                <div class="item-info">
                    <h3>Suối nguồn tươi trẻ</h3>
                    <p><i class='bx bx-calendar'></i> 10/05/2026 • 3.1 MB</p>
                </div>
            </div>

            <!-- Item 3 -->
            <div class="gallery-item" data-category="macro">
                <div class="img-container">
                    <img src="https://images.unsplash.com/photo-1550159930-40066082a4fc?auto=format&fit=crop&w=600&q=80" alt="Sương sớm">
                    <div class="overlay">
                        <i class='bx bx-search-alt-2'></i>
                    </div>
                </div>
                <div class="item-info">
                    <h3>Giọt sương mai</h3>
                    <p><i class='bx bx-calendar'></i> 09/05/2026 • 1.8 MB</p>
                </div>
            </div>

            <!-- Item 4 -->
            <div class="gallery-item" data-category="landscape">
                <div class="img-container">
                    <img src="https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=600&q=80" alt="Cánh đồng">
                    <div class="overlay">
                        <i class='bx bx-search-alt-2'></i>
                    </div>
                </div>
                <div class="item-info">
                    <h3>Đồng xanh bát ngát</h3>
                    <p><i class='bx bx-calendar'></i> 05/05/2026 • 4.2 MB</p>
                </div>
            </div>
            
            <!-- Item 5 -->
            <div class="gallery-item" data-category="macro">
                <div class="img-container">
                    <img src="https://images.unsplash.com/photo-1418065460487-3e41a6c8e1e4?auto=format&fit=crop&w=600&q=80" alt="Rừng thu">
                    <div class="overlay">
                        <i class='bx bx-search-alt-2'></i>
                    </div>
                </div>
                <div class="item-info">
                    <h3>Rừng lá kim</h3>
                    <p><i class='bx bx-calendar'></i> 02/05/2026 • 2.9 MB</p>
                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript Xử lý bộ lọc đơn giản -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const filterBtns = document.querySelectorAll('.filter-group button');
            const galleryItems = document.querySelectorAll('.gallery-item');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Xóa class active ở tất cả các nút
                    filterBtns.forEach(b => b.classList.remove('active'));
                    // Thêm class active vào nút được click
                    btn.classList.add('active');

                    const filterValue = btn.getAttribute('data-filter');

                    // Lọc ảnh
                    galleryItems.forEach(item => {
                        if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>