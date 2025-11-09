# Nastavení apache pro localhost
## Umožnění hezkých adres a nastavení adresáře public jako Root
### 1. `C:\xampp\apache\conf\httpd.conf`
Odkomentovat
```
Include conf/extra/httpd-vhosts.conf

#Include conf/extra/httpd-vhosts.conf
```
### 2. `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
Volžení tohoto bloku na konec souboru
```
# Virtuální hostitel pro MVC aplikaci
<VirtualHost *:80>
    # Adresa, kterou budeme zadávat do prohlížeče
    ServerName web.local

    # Cesta ke složce "public" - POZOR na lomítka, musí být /
    DocumentRoot "C:/xampp/htdocs/web/public" 

    # Nastavení oprávnění pro adresář
    <Directory "C:/xampp/htdocs/web/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # (Volitelné) Kam ukládat logy chyb a přístupů pro tento web
    ErrorLog "logs/web.local-error.log"
    CustomLog "logs/web.local-access.log" common
</VirtualHost>
```
### 3. `C:\Windows\System32\drivers\etc\hosts`
Přidat námi zvolený lokální název mezi hosty, abychom mohli směrovat do adresáře _public_ jako _Document Root_
```
127.0.0.1   web.local
```
### 4. `.htaccess`
V public adresáři musí být vedle index.php přítomen .htaccess s obsahem:
```
<IfModule mod_rewrite.c>
    # Zapne mod_rewrite
    RewriteEngine On

    # Pokud požadavek míří na existující soubor (např. CSS, JS, obrázek), neprováděj přepis
    # Celý řádek říká: "Proveď další krok, POKUD požadovaná cesta NENÍ existující soubor."
    RewriteCond %{REQUEST_FILENAME} !-f

    # Pokud požadavek míří na existující adresář, neprováděj přepis
    # Celý řádek říká: "Proveď další krok, POKUD požadovaná cesta NENÍ existující adresář."
    RewriteCond %{REQUEST_FILENAME} !-d

    # Přepiš všechny ostatní požadavky na index.php.
    # Parametr [L] znamená "Last Rule" a [QSA] znamená "Query String Append" (připojí parametry)
    RewriteRule ^(.*)$ index.php [L,QSA]
</IfModule>
```