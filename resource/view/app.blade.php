<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đặt Đồ Ăn Nhanh</title>

    <!-- jQuery + jQuery Mobile CDN -->
    <link rel="stylesheet"
          href="<?=source('jquery/jquery.mobile-1.4.5.min.css')?>">
    <script src="<?=source('jquery/jquery-1.11.1.min.js')?>"></script>
    <script src="<?=source('jquery/jquery.mobile-1.4.5.min.js')?>"></script>

    <style>
        .ui-li-desc { white-space: normal; }
        .total { font-weight:bold; color:#c00; }
    </style>
</head>
<body>

<!-- ================== Trang 1: Danh sách món ================== -->
<div data-role="page" id="menu">
    <div data-role="header" data-theme="b">
        <h1>Menu Nhà Hàng</h1>
    </div>

    <div role="main" class="ui-content">
        <ul data-role="listview" data-inset="true" id="foodList">
            <!-- Món sẽ được chèn bằng JS -->
        </ul>

        <div style="margin-top:20px;">
            <a href="#cart" data-role="button" data-icon="shopping-cart"
               data-transition="slide">Xem giỏ hàng (<span id="cartCount">0</span>)</a>
        </div>
    </div>

    <div data-role="footer">
        <h4>© 2025 Nhà Hàng Nhanh</h4>
    </div>
</div>

<!-- ================== Trang 2: Giỏ hàng ================== -->
<div data-role="page" id="cart">
    <div data-role="header" data-theme="b">
        <h1>Giỏ hàng</h1>
    </div>

    <div role="main" class="ui-content">
        <ul data-role="listview" data-inset="true" id="cartList">
            <!-- Giỏ sẽ được render ở đây -->
        </ul>

        <div id="totalBox" class="total" style="margin:15px 0; display:none;">
            Tổng cộng: <span id="totalPrice">0</span> ₫
        </div>

        <button id="checkoutBtn" data-role="button" data-theme="a"
                data-icon="check" style="display:none;">Đặt hàng ngay</button>
    </div>

    <div data-role="footer">
        <a href="#menu" data-role="button" data-icon="arrow-l"
           data-rel="back">Quay lại menu</a>
    </div>
</div>

<!-- ================== Trang 3: Chọn bàn ================== -->
<div data-role="page" id="selectTable">
  <div data-role="header" data-theme="b"><h1>Chọn bàn phục vụ</h1></div>
  <div role="main" class="ui-content">
    <label for="tableSelect">Chọn bàn:</label>
    <select id="tableSelect" data-native-menu="false">
      <option value="">-- Chọn bàn --</option>
      <optgroup label="Tầng 1">
        <option value="t1-01">T1-01</option>
        <option value="t1-02">T1-02</option>
        <!-- thêm tới t1-12 -->
      </optgroup>
      <optgroup label="Tầng 2">
        <option value="t2-01">T2-01</option>
        <!-- thêm tới t2-16 -->
      </optgroup>
      <optgroup label="Tầng 3">
        <option value="t3-01">T3-01</option>
        <!-- thêm tới t3-10 -->
      </optgroup>
    </select>

    <label for="paymentSelect">Hình thức thanh toán:</label>
    <select id="paymentSelect" data-native-menu="false">
      <option value="">-- Chọn hình thức --</option>
      <option value="cash">Thanh toán tại bàn</option>
      <option value="vnpay">Ví điện tử VNPAY</option>
    </select>

    <a href="#" id="confirmOrderBtn" data-role="button" data-theme="a" data-icon="check">Xác nhận đặt hàng</a>
  </div>
</div>

<!-- ================== Trang 4: Xác nhận ================== -->
<div data-role="page" id="orderDone">
  <div data-role="header" data-theme="b"><h1>Trạng thái đơn hàng</h1></div>
  <div role="main" class="ui-content" id="orderStatus">
    <h3>Đang xử lý...</h3>
    <a href="#menu" data-role="button" data-icon="home" data-theme="b" id="backToMenuBtn" style="margin:16px 0 0 0;">Về trang đầu</a>
  </div>
</div>


<script>
/* -------------------------------------------------
   DỮ LIỆU MÓN ĂN (có thể lấy từ server sau này)
   ------------------------------------------------- */
const foods = [
    { id:1, name:"Phở bò tái", price:45000, img:"<?=source("pho_bo_tai.jpg")?>" },
    { id:2, name:"Bánh mì thịt nướng", price:25000, img:"<?=source("banh_mi_thit_nuong.webp")?>" },
    { id:3, name:"Cơm tấm sườn", price:40000, img:"<?=source("com_tam_suon.jpeg")?>" },
    { id:4, name:"Trà sữa trân châu", price:30000, img:"<?=source("tra_sua_tran_chau.jpg")?>" },
    { id:5, name:"Gỏi cuốn tôm thịt", price:35000, img:"<?=source("goi_cuon_tom_thit.jpg")?>" }
];

/* -------------------------------------------------
   GIỎ HÀNG (lưu trong localStorage để giữ khi đổi trang)
   ------------------------------------------------- */
let cart = JSON.parse(localStorage.getItem('foodCart')) || [];

/* -------------------------------------------------
   HÀM VẼ MENU
   ------------------------------------------------- */
function renderMenu() {
    const $list = $('#foodList').empty();
    foods.forEach(f => {
        const qty = (cart.find(c=>c.id===f.id)||{}).qty || 0;
        const li = `
            <li>
                <img src="${f.img}" style="float:left;margin-right:10px;">
                <h3>${f.name}</h3>
                <p class="ui-li-desc">${f.price.toLocaleString()} ₫</p>
                <div style="margin-top:8px;">
                    <button class="minus" data-id="${f.id}" ${qty==0?'disabled':''}>-</button>
                    <span class="qty">${qty}</span>
                    <button class="plus" data-id="${f.id}">+</button>
                </div>
            </li>`;
        $list.append(li);
    });
    $list.listview('refresh');
}

/* -------------------------------------------------
   HÀM VẼ GIỎ HÀNG
   ------------------------------------------------- */
function renderCart() {
    const $list = $('#cartList').empty();
    let total = 0;
    const items = cart.filter(c=>c.qty>0);
    if (items.length===0) {
        $list.append('<li><h3>Giỏ hàng trống</h3></li>');
    } else {
        items.forEach(c => {
            const f = foods.find(x=>x.id===c.id);
            const lineTotal = f.price * c.qty;
            total += lineTotal;
            const li = `
                <li>
                    <img src="${f.img}" style="float:left;margin-right:10px;">
                    <h3>${f.name}</h3>
                    <p>${f.price.toLocaleString()} ₫ × <b>${c.qty}</b> = ${lineTotal.toLocaleString()} ₫</p>
                    <div style="margin-top:8px;">
                        <button class="minus" data-id="${c.id}">-</button>
                        <span class="qty">${c.qty}</span>
                        <button class="plus" data-id="${c.id}">+</button>
                    </div>
                </li>`;
            $list.append(li);
        });
    }
    $('#totalPrice').text(total.toLocaleString());
    $('#totalBox, #checkoutBtn').toggle(items.length>0);
    $('#cartCount').text(items.reduce((s,i)=>s+i.qty,0));
    $list.listview('refresh');
}

/* -------------------------------------------------
   XỬ LÝ THÊM / BỚT
   ------------------------------------------------- */
$(document).on('click', '.plus, .minus', function () {
    const id = parseInt($(this).data('id'));
    const isPlus = $(this).hasClass('plus');
    let item = cart.find(c=>c.id===id);
    if (!item) { item = {id, qty:0}; cart.push(item); }

    if (isPlus) item.qty++;
    else if (item.qty>0) item.qty--;

    // Xóa nếu qty=0
    if (item.qty===0) cart = cart.filter(c=>c.id!==id);

    localStorage.setItem('foodCart', JSON.stringify(cart));
    renderMenu();
    renderCart();
});

/* -------------------------------------------------
   ĐẶT HÀNG (demo: alert + reset)
   ------------------------------------------------- */
$('#checkoutBtn').on('click', function () {
    const order = cart.map(c=>{
        const f = foods.find(x=>x.id===c.id);
        return `${f.name} × ${c.qty}`;
    }).join(', ');
    alert('Đơn hàng đã được gửi!\n' + order + '\nTổng: ' + $('#totalPrice').text() + ' ₫');
    cart = [];
    localStorage.removeItem('foodCart');
    renderMenu();
    renderCart();
});

/* -------------------------------------------------
   CHUYỂN ĐẾN CHỌN BÀN KHI CHECKOUT
   ------------------------------------------------- */
$('#checkoutBtn').off('click').on('click', function () {
    if (cart.length === 0) return;
    $.mobile.changePage('#selectTable', {transition:'slide'});
});

/* -------------------------------------------------
   XÁC NHẬN ĐƠN HÀNG
   ------------------------------------------------- */
$('#confirmOrderBtn').on('click', function () {
    const table = $('#tableSelect').val();
    const payment = $('#paymentSelect').val();

    if (!table || !payment) {
        alert('Vui lòng chọn đầy đủ bàn và hình thức thanh toán!');
        return;
    }

    const total = $('#totalPrice').text().replace(/\D/g,'');
    const orderData = {
        table,
        payment,
        cart,
        total: parseInt(total)
    };

    // Lưu tạm để vnpay demo có thể đọc
    localStorage.setItem('pendingOrder', JSON.stringify(orderData));

    if (payment === 'vnpay') {
        // Chuyển hướng đến demo thanh toán
        window.location.href = 'vnpay_demo.php?amount=' + total;
    } else {
        // Gửi thẳng đơn hàng về server
        $.post('order_api.php', orderData, function (res) {
            $('#orderStatus').html('<h3>' + res.message + '</h3>');
            $.mobile.changePage('#orderDone', {transition:'slide'});
            cart = [];
            localStorage.removeItem('foodCart');
        }, 'json').fail(() => alert('Không gửi được đơn hàng.'));
    }
});

/* -------------------------------------------------
   KHỞI TẠO KHI MỖI PAGE ĐƯỢC TẢI
   ------------------------------------------------- */
$(document).on('pagecreate', '#menu', renderMenu);
$(document).on('pagecreate', '#cart', renderCart);

$('#orderDone').on('click', '#backToMenuBtn', function() {
    // Reset trạng thái đơn hàng nếu cần
    $('#orderStatus').html('<h3>Đang xử lý...</h3><a href="#menu" data-role="button" data-icon="home" data-theme="b" id="backToMenuBtn" style="margin:16px 0 0 0;">Về trang đầu</a>');
    // Chuyển về menu (jQuery Mobile sẽ tự xử lý)
    // Nếu muốn reload lại menu, có thể gọi renderMenu();
});
</script>

</body>
</html>