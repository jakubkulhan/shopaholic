# Shopaholic

Shopaholic is an open-source e-shop based on PHP with [Nette Framework](http://nettephp.com/) and [dibi](http://dibiphp.com/ "tiny'n'smart database layer") under the hood. It aims to be the easiest, yet quite useful, e-shop platform ever made.

## Installation

- copy all files into your document root
- import `my.sql` into your database (it's for MySQL; if you have some other database, try to change SQL to dialect your DB undestands)
- edit `.htaccess`
- check configuration in `conf/` subdirectory (especially target at `common.php`, `db.php` and `timezone.php`, the others are possible to edit in administration)
- make directories `media/` and `tmp/` if not present
- and in the end change permission to `777` on `conf/`, `media/` and `tmp/`

## Usage

Open your browser and shop. To enter administration add suffix `admin` into the urlbar. For example if your shop resides at `http://example.com/my/super/duper/sexy/shop/`, admin is at `http://example.com/my/super/duper/sexy/shop/admin/`. I think you get the idea.
