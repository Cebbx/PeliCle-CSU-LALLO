# Step-by-Step Hostinger Deployment Guide para sa PeliCle (Laravel 12 + Filament)

Naihanda na ang buong system para sa Hostinger (naka-compile na ang `public/build` assets, may root `.htaccess`, at may `/hostinger-setup` one-click browser setup).

---

### Hakbang 1: Ihanda ang PHP Version sa Hostinger hPanel
1. Mag-login sa inyong **Hostinger hPanel**.
2. Pumunta sa **Websites** &rarr; i-click ang **Manage** sa inyong domain.
3. Sa kaliwang menu, pumunta sa **Advanced** &rarr; **PHP Configuration**.
4. Piliin ang **PHP 8.2** (o **PHP 8.3**), at i-click ang **Save**.
5. Sa tab na **PHP Extensions**, tiyaking naka-enable (naka-check) ang mga sumusunod:
   - `intl`
   - `zip`
   - `gd`
   - `fileinfo`
   - `pdo_sqlite`
   - `pdo_mysql`
   - `bcmath`

---

### Hakbang 2: I-upload ang Files sa File Manager
1. I-zip ang buong folder ng proyektong ito.
   *(Siguraduhing kasama ang `vendor`, `public/build`, `.htaccess`, at `app` folders).*
2. Sa Hostinger hPanel, pumunta sa **Files** &rarr; **File Manager**.
3. Buksan ang folder ng inyong domain (karaniwan ay `public_html/`).
4. I-upload ang `.zip` file at i-click ang **Extract**.
5. Dahil nilagyan na natin ng root `.htaccess`, awtomatiko nang ididirekta ng Hostinger ang mga bibisita sa `/public` folder nang ligtas.

---

### Hakbang 3: I-configure ang `.env` File
1. Sa loob ng `public_html/`, hanapin ang `.env` file (o i-copy ang `.env.example` at pangalanang `.env`).
2. I-edit ang mga sumusunod na linya:
   ```env
   APP_NAME="PeliCle"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://ang-domain-ninyo.com
   ```
3. Para sa Database:
   - **Opsyon A (Pinakamadali - SQLite):**
     ```env
     DB_CONNECTION=sqlite
     ```
   - **Opsyon B (Hostinger MySQL):**
     Gumawa ng MySQL Database sa hPanel (**Databases** &rarr; **Management**), at ilagay ang credentials:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=u123456789_pelicle
     DB_USERNAME=u123456789_user
     DB_PASSWORD=ang_inyong_password
     ```

---

### Hakbang 4: One-Click Setup sa Browser (Walang kailangang SSH)
1. Buksan ang inyong browser at pumunta sa:
   ```
   https://ang-domain-ninyo.com/hostinger-setup
   ```
2. Awtomatikong patatakbuhin ng link na ito ang:
   - `php artisan storage:link` (para lumabas ang mga uploaded documents at signatures)
   - `php artisan migrate --force` (para maihanda ang database tables)
   - `php artisan optimize:clear` (para malinis ang view at route cache)
3. Kapag nakita ang mensaheng **Setup Status: SUCCESS**, i-click lang ang **Go to Application** at live na po ang inyong web app!
