<?php
/**
 * Russian system setting lexicon for mxLocDoc.
 *
 * @package mxlocdoc
 * @subpackage lexicon
 */
$_lang['area_mxlocdoc:filesystem'] = 'Файловая система';
$_lang['area_mxlocdoc:navigation'] = 'Навигация';
$_lang['area_mxlocdoc:search'] = 'Поиск';
$_lang['area_mxlocdoc:cache'] = 'Кеш';

$_lang['setting_mxlocdoc.docs_path'] = 'Путь к документации';
$_lang['setting_mxlocdoc.docs_path_desc'] = 'Путь к локальной папке с Markdown-документацией относительно MODX core. Можно использовать placeholder [[+corePath]], например [[+corePath]]components/mxlocdoc/docs/. Публичным URL не является.';

$_lang['setting_mxlocdoc.languages'] = 'Языки документации';
$_lang['setting_mxlocdoc.languages_desc'] = 'Список кодов языков через запятую (например en,ru). Пакет включает мультиязычный режим и ищет подпапки с этими кодами внутри папки документации. Пусто — мультиязычность выключена, документация читается из корня как есть.';

$_lang['setting_mxlocdoc.default_file'] = 'Файл по умолчанию';
$_lang['setting_mxlocdoc.default_file_desc'] = 'Markdown-файл, который открывается при входе в документацию без указанного пути.';

$_lang['setting_mxlocdoc.nav_file'] = 'Файл навигации';
$_lang['setting_mxlocdoc.nav_file_desc'] = 'Manifest-файл навигации внутри папки документации, например _sidebar.json или mxlocdoc.json.';

$_lang['setting_mxlocdoc.search_enabled'] = 'Включить поиск';
$_lang['setting_mxlocdoc.search_enabled_desc'] = 'Разрешает поиск по Markdown-файлам документации в manager UI.';

$_lang['setting_mxlocdoc.cache_ttl'] = 'TTL кеша';
$_lang['setting_mxlocdoc.cache_ttl_desc'] = 'Время жизни кеша навигации и поиска в секундах. 0 отключает TTL-кеширование.';

$_lang['setting_mxlocdoc.max_file_size'] = 'Максимальный размер файла';
$_lang['setting_mxlocdoc.max_file_size_desc'] = 'Максимальный размер читаемого Markdown-файла или ассета в байтах.';

$_lang['setting_mxlocdoc.allowed_asset_extensions'] = 'Разрешенные расширения ассетов';
$_lang['setting_mxlocdoc.allowed_asset_extensions_desc'] = 'Список расширений файлов, которые можно отдавать из Markdown через connector, через запятую.';
