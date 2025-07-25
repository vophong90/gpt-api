FROM php:8.2-apache

# Copy file PHP vào thư mục web
COPY public/ /var/www/html/

EXPOSE 80
