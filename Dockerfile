# Imagem base com PHP e Apache
FROM php:8.1-apache

# Copia os arquivos para o diretório padrão do Apache
COPY . /var/www/html/

# Expõe a porta 80 para o serviço web
EXPOSE 80

# Inicia o Apache
CMD ["apache2-foreground"]
