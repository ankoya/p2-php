<?php
/*
    p2 - スレッド表示でincludeされるファイル
*/

// ImageCache2が有効の場合
if (in_array($_conf['expack.ic2.enabled'], array($_conf['ktai'] ? 1 : 2, 3))) {
// 置換画像URL
    if ($_conf['expack.ic2.enabled'] >= 2) {
        require_once P2_LIB_DIR . '/wiki/ReplaceImageUrlCtl.php';
        $GLOBALS['replaceImageUrlCtl'] = new ReplaceImageUrlCtl();
    }
}
// 携帯ビュー以外の場合
if (!$_conf['ktai'] || $_conf['iphone']) {
// リンクプラグイン
    require_once P2_LIB_DIR . '/wiki/LinkPluginCtl.php';
    $GLOBALS['linkPluginCtl'] = new LinkPluginCtl();
}
// 置換ワード
require_once P2_LIB_DIR . '/wiki/ReplaceWordCtl.php';
$GLOBALS['replaceWordCtl'] = new ReplaceWordCtl();
