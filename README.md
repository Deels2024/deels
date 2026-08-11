# Сборка ассетов DEELS

В layout `resources/views/layouts/neon/header.blade.php` подключается один общий CSS-файл:

```blade
<link rel="stylesheet" href="{{ext_asset('/dist/css/app.min.css')}}"/>
```

В layout `resources/views/layouts/neon/footer.blade.php` подключается один общий JS-файл:

```blade
<script src="{{ext_asset('/dist/js/app.min.js')}}"></script>
```

## Команды сборки

```bash
npm run build:neon-css
npm run build:neon-js
```

Эти команды запускают:

```bash
node scripts/build-neon-css.js
node scripts/build-neon-js.js
```

## Watch-режим

Во время разработки можно запустить watchers:

```bash
npm run watch:neon
```

Эта команда запускает CSS и JS watchers одновременно.

Также можно запускать их отдельно:

```bash
npm run watch:neon-css
npm run watch:neon-js
```

Они следят за исходными файлами из списков bundle и автоматически пересобирают:

```text
public/dist/css/app.min.css
public/dist/css/app.min.css.map
public/dist/js/app.min.js
public/dist/js/app.min.js.map
```

Watchers не следят за итоговыми bundle-файлами, чтобы запись результата не запускала бесконечную пересборку.

## Исходные CSS-файлы

Порядок важен. Он соответствует прежнему порядку подключения CSS в `header.blade.php`:

```text
public/dist/css/owl.carousel.min.css
public/dist/css/owl.theme.default.min.css
public/dist/css/font/stylesheet.css
public/dist/css/magnific-popup.css
public/dist/css/admin_style.css
public/dist/css/kpromo_slider.css
public/js/libs/fancybox/jquery.fancybox.min.css
public/dist/css/admin-top.css
public/dist/css/app.css
public/dist/css/main.css
```

## Исходные JS-файлы

Порядок важен. Он соответствует прежнему порядку подключения JS в `footer.blade.php`:

```text
public/js/jquery-3.6.0.min.js
public/dist/js/nouislider.min.js
public/dist/js/slick.min.js
public/dist/js/owl.carousel.min.js
public/dist/js/jquery.magnific-popup.min.js
public/dist/js/script.js
public/dist/js/admin_scripts.js
```

`window.DeelsFooterConfig` остается отдельным inline JSON-конфигом перед JS bundle. Это нужно, потому что код из `script.js` читает этот объект при загрузке.

## Что делают сборщики

Скрипт `scripts/build-neon-css.js`:

- читает CSS-файлы из списка;
- объединяет их в один файл;
- переписывает относительные `url(...)` в абсолютные public-пути;
- минифицирует CSS построчно;
- записывает результат в `public/dist/css/app.min.css`;
- создает source map `public/dist/css/app.min.css.map`.

Скрипт `scripts/build-neon-js.js`:

- читает JS-файлы из списка;
- объединяет их в один файл;
- добавляет разделители между файлами, чтобы избежать ошибок склейки;
- удаляет старые `sourceMappingURL` из исходных файлов;
- записывает результат в `public/dist/js/app.min.js`;
- создает source map `public/dist/js/app.min.js.map`.

CSS-сборщик переписывает относительные пути для шрифтов и картинок. Например, путь из `public/dist/css/font/stylesheet.css`:

```css
url('Gilroy-Regular.woff2')
```

после сборки становится:

```css
url("/dist/css/font/Gilroy-Regular.woff2")
```

## Когда запускать

Запускайте сборку после изменения любого исходного CSS или JS из списков выше:

```bash
npm run build:neon-css
npm run build:neon-js
```

После сборки нужно закоммитить измененный исходный файл и обновленные bundle-файлы:

```text
public/dist/css/app.min.css
public/dist/css/app.min.css.map
public/dist/js/app.min.js
public/dist/js/app.min.js.map
```

## Source map

Bundle-файлы содержат ссылки на source maps:

```css
/*# sourceMappingURL=app.min.css.map */
```

```js
//# sourceMappingURL=app.min.js.map
```

Браузерные DevTools используют source maps, чтобы показывать, из какого исходного CSS или JS-файла пришло правило или строка кода.

## Добавление нового CSS или JS

Чтобы добавить новый файл в bundle:

1. Добавьте путь в массив `files` внутри `scripts/build-neon-css.js` или `scripts/build-neon-js.js`.
2. Разместите его в нужном месте списка с учетом каскада CSS или порядка JS-зависимостей.
3. Запустите нужную сборку: `npm run build:neon-css` или `npm run build:neon-js`.
4. Проверьте страницу, которая использует `layouts/neon/header.blade.php` или `layouts/neon/footer.blade.php`.

Не подключайте новый общий CSS или JS отдельным тегом в layout, если он должен быть частью общего Neon bundle.
