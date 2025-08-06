# Imagem base com PHP e Apache
FROM php:8.1-apache

# Copia os arquivos para o diretório padrão do Apache
COPY . /var/www/html/

# Define rss-proxy.php como página inicial
RUN echo "DirectoryIndex rss-proxy.php" >> /etc/apache2/apache2.conf

EXPOSE 80
CMD ["apache2-foreground"]
