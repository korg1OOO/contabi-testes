# Contabi

Sistema web para gestão de clientes, marcas, patentes, prazos, documentos, relatórios, notificações, busca de colidências e despachos da RPI.

Este guia descreve a configuração para desenvolvimento local, especialmente com XAMPP no Windows.

## Versão online

[https://contabi.unaux.com/](https://contabi.unaux.com/)

Contas de acesso:

| Perfil | CPF | Senha |
| --- | --- | --- |
| Administrador (adm) | `00000000000` | `Admin@123` |
| Conta comum | `12345678901` | `Consultor@123` |

## Para rodar localmente

### Requisitos

- PHP 8 ou superior, com as extensões `pdo_mysql` e `zip` habilitadas. As extensões `iconv` e `zlib` também são usadas pelo parser de PDF.
- MySQL ou MariaDB iniciado localmente.
- Composer instalado e disponível no terminal.
- Acesso ao terminal na pasta raiz do projeto.

No XAMPP, o arquivo de configuração do PHP normalmente fica em `C:\xampp\php\php.ini`.

### Passo a passo

#### 1. Inicie o banco de dados

Abra o XAMPP Control Panel e inicie o MySQL. Se o MySQL tiver usuário ou senha diferentes dos padrões locais, use esses dados na configuração da aplicação, no passo 3.

#### 2. Ajuste o PHP para importar arquivos grandes

Confira qual `php.ini` o PHP do terminal está usando:

```bash
php --ini
```

No `php.ini` correspondente, habilite as extensões necessárias. No XAMPP, remova o `;` inicial das linhas existentes, se estiverem comentadas:

```ini
extension=pdo_mysql
extension=zip
```

Defina também os limites usados para importar arquivos RPI grandes:

```ini
upload_max_filesize = 256M
post_max_size = 300M
memory_limit = 1024M
max_execution_time = 600
max_input_time = 600
```

Salve o arquivo e reinicie o terminal e o servidor PHP depois de qualquer alteração.

Confira o PHP e as extensões:

```bash
php -v
php -r "echo extension_loaded('pdo_mysql') ? 'PDO MySQL OK' : 'PDO MySQL AUSENTE';"
php -r "echo class_exists('ZipArchive') ? 'ZIP OK' : 'ZIP ERRO';"
```

Se o comando `php` não for reconhecido ou apontar para outro PHP, inclua `C:\xampp\php` no `PATH` do Windows ou execute os comandos usando o caminho completo do `php.exe` do XAMPP.

#### 3. Crie a configuração local

O arquivo `app/config/Config.php` é local e está ignorado pelo Git. Na primeira instalação, copie o exemplo uma vez:

No PowerShell:

```powershell
Copy-Item app/config/Config.php.example app/config/Config.php
```

No macOS ou Linux:

```bash
cp app/config/Config.php.example app/config/Config.php
```

Edite `app/config/Config.php` e confira os valores de `URL_BASE`, `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASS`. O exemplo já aponta para o banco `db_contabi`, host `localhost`, usuário `root` sem senha e endereço `http://localhost:8080`; ajuste-os se a sua instalação local for diferente. Não substitua um `Config.php` local que já esteja funcionando.

**A porta em `URL_BASE` deve ser a mesma porta em que o servidor PHP será iniciado.** A configuração local já existente nesta máquina usa `http://localhost:1644`; se mantiver esse valor, use a porta `1644` no passo 6. Em uma instalação limpa, o arquivo de exemplo usa a porta `8080`.

Não inclua sua senha local do banco em arquivos versionados nem a compartilhe ao enviar alterações.

#### 4. Instale as dependências do Composer

Na raiz do projeto, execute:

```bash
composer install
```

Esse comando cria `vendor/` e instala o `smalot/pdfparser`, usado na leitura dos PDFs da RPI. Confira se o autoload e o parser estão disponíveis:

```bash
php -r "require 'vendor/autoload.php'; echo class_exists('Smalot\\PdfParser\\Parser') ? 'PDF OK' : 'PDF ERRO';"
```

#### 5. Prepare o banco de dados

Importe `app/database/scripts/script.sql` usando o phpMyAdmin do XAMPP ou o cliente MySQL. Normalmente o phpMyAdmin fica em `http://localhost/phpmyadmin`; o endereço pode variar se a porta do Apache do XAMPP foi alterada.

No phpMyAdmin, use a opção **Importar** e selecione `app/database/scripts/script.sql`. O script cria `db_contabi`, as tabelas e dados iniciais. As credenciais de conexão informadas em `Config.php` precisam ter permissão para criar e acessar o banco.

> **Atenção:** o script começa com `DROP DATABASE IF EXISTS db_contabi`. Importá-lo novamente apaga e recria esse banco, removendo os dados locais que já existirem. Use-o somente para uma instalação local nova ou depois de fazer backup.

Ao conectar, a aplicação também tenta inicializar o banco ausente e aplica automaticamente a migração da coluna `prazos.origem`. Ainda assim, importar o script explicitamente é o caminho recomendado para a primeira configuração.

#### 6. Inicie a aplicação

Abra o terminal na raiz do projeto e escolha a porta que está em `URL_BASE` no `app/config/Config.php`.

Para `http://localhost:8080`:

```bash
php -S localhost:8080 -t public public/index.php
```

Para a configuração local atual desta máquina, que usa `http://localhost:1644`:

```bash
php -S localhost:1644 -t public public/index.php
```

Deixe o terminal aberto enquanto usar o sistema. Acesse no navegador o mesmo endereço configurado, por exemplo `http://localhost:1644`.

#### 7. Entre no sistema

O banco inicial inclui contas de demonstração. Use uma delas para acessar a tela de login:

| Perfil | CPF | Senha |
| --- | --- | --- |
| Administrador | `00000000000` | `Admin@123` |
| Consultor | `12345678901` | `Consultor@123` |

Essas contas são apenas para desenvolvimento local. Troque as senhas se for manter dados reais no ambiente.

### Arquivos enviados

Os documentos e arquivos importados são armazenados sob `public/assets/uploads`. A aplicação cria os diretórios necessários; o usuário que executa o PHP precisa ter permissão de escrita nesse local.

### Problemas comuns

- **Redirecionamentos ou CSS apontando para a porta errada:** confira `URL_BASE` em `Config.php` e use a mesma porta no comando `php -S`.
- **Erro de conexão com MySQL:** confirme que o serviço está iniciado, que `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASS` estão corretos e que o usuário pode acessar `db_contabi`.
- **Erro de `ZipArchive` ao importar RPI:** habilite `extension=zip` no `php.ini` usado pelo terminal e reinicie o servidor PHP.
- **PDF não é lido:** execute `composer install` e confirme que o comando de verificação do parser retorna `PDF OK`.
- **Upload interrompido ou arquivo grande rejeitado:** confira os limites de upload e tempo no `php.ini` da instância PHP que está atendendo a aplicação; reinicie o servidor após alterar o arquivo.
- **Pasta de uploads sem permissão:** conceda escrita em `public/assets/uploads` ao usuário que executa o PHP.
