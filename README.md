# Contabi

Sistema web para gestão de clientes, marcas, patentes, prazos, documentos, relatórios, notificações, busca de colidências e despachos da RPI.

## Lembretes para rodar local

### 1. Banco de dados

Criar o banco com o script.sql

Ajustar o config.php de acordo com o banco em app/config/Config.php

### 2. Composer

Na raiz do projeto, executar composer install para instalar as dependencias na pasta vendor/ (senão não funciona a leitura de pdf da RPI)

### 3. Extensão ZIP do PHP

Em C:\xampp\php\php.ini tirar ; de ;extension=zip

Outros updates no php.ini pra leitura não ter problemas:
upload_max_filesize = 256M
post_max_size = 300M
memory_limit = 1024M
max_execution_time = 600
max_input_time = 600

Verificar com php -r "echo class_exists('ZipArchive') ? 'ZIP OK' : 'ZIP ERRO';"

E testar pdf com php -r "require 'vendor/autoload.php'; echo class_exists('Smalot\\PdfParser\\Parser') ? 'PDF OK' : 'PDF ERRO';"

### 4. Rodar

php -S localhost:8080 -t public public/index.php

Abrir em http://localhost:8080

Só alegria