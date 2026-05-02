# Sử dụng phiên bản PHP bạn cần (ví dụ 8.5)
FROM php:8.0-fpm-alpine

# Cài đặt các thư viện hệ thống cần thiết
# BỔ SUNG: linux-headers để sửa lỗi biên dịch extension sockets
RUN apk add --no-cache \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    openssl-dev \
    linux-headers 

# Bây giờ lệnh này sẽ chạy thành công
RUN docker-php-ext-install pdo pdo_mysql mbstring sockets bcmath

# Cài đặt Composer chính thức
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy các file cấu hình trước để tối ưu hóa việc cache layer của Docker
COPY composer.json composer.lock ./

# Cài đặt thư viện (không cài dev dependencies để nhẹ Image khi production)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy toàn bộ mã nguồn vào container
COPY . .

# Chạy các script hậu cài đặt của Composer (như @CopyEnv bạn đã định nghĩa)
RUN composer run-script post-install-cmd

# Cấp quyền cho user www-data để server có thể ghi log/resource
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["php-fpm"]