<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */
/**
 * tGrep ���C�Ƀ��X�g���j���[
 */

if ($_conf['ktai']) {
    tgrep_print_quick_list_k();
} else {
    tgrep_print_quick_list();
}

/**
 * ���C�Ƀ��X�g��ǂݍ���
 */
function tgrep_read_quick_list()
{
    global $_conf;

    if (file_exists($_conf['expack.tgrep.quick_file'])) {
        return array_filter(array_map('trim', (array) @file($_conf['expack.tgrep.quick_file'])), 'strlen');
    }
    return array();
}

/**
 * PC�p�\��
 */
function tgrep_print_quick_list()
{
    global $_conf;

    $tgrep_quick_list = tgrep_read_quick_list();

    if (!defined('TGREP_SMARTLIST_PRINT_ONLY_LINKS')) {
        echo '<div class="menu_cate">' . "\n";
        echo '<b><a class="menu_cate" href="#" onclick="return showHide(\'c_tgrep_quick\');" target="_self">�X���ꔭ����</a></b>' . "\n";
        echo '[<a href="#" onclick="return tGrepAppendListInput(\'quick\',\'c_tgrep_quick\');" target="_self">�{</a>]' . "\n";
        echo '[<a href="#" onclick="return tGrepClearList(\'quick\',\'c_tgrep_quick\');" target="_self">��</a>]' . "\n";
        echo '<div class="itas" id="c_tgrep_quick">' . "\n";
    }
    if ($tgrep_quick_list) {
        foreach ($tgrep_quick_list as $tgrep_quick_query) {
            $tgrep_quick_query_en = rawurlencode($tgrep_quick_query);
            $tgrep_quick_query_ht = htmlspecialchars($tgrep_quick_query, ENT_QUOTES);
            echo '<a class="fav" href="#" onclick="return tGrepRemoveListItem(\'quick\',\'c_tgrep_quick\',\'' . $tgrep_quick_query_en . '\');" target="_self">��</a>' . "\n";
            echo '<a href="tgrepc.php?Q=' . $tgrep_quick_query_en . '">' . $tgrep_quick_query_ht . '</a><br>' . "\n";
        }
    } else {
        echo "�i�Ȃ��j\n";
    }
    if (!defined('TGREP_SMARTLIST_PRINT_ONLY_LINKS')) {
        echo "</div>\n</div>\n";
    }
}

/**
 * �g�їp�\��
 */
function tgrep_print_quick_list_k()
{
    global $_conf;

    $tgrep_quick_list = tgrep_read_quick_list();

    echo '<h4>�ꔭ����</h4>' . "\n";
    if ($tgrep_quick_list) {
        echo '<ul>' . "\n";
        foreach ($tgrep_quick_list as $tgrep_quick_query) {
            $tgrep_quick_query_en = rawurlencode($tgrep_quick_query);
            $tgrep_quick_query_ht = htmlspecialchars($tgrep_quick_query, ENT_QUOTES);
            echo '<li><a href="tgrepc.php?Q=' . $tgrep_quick_query_en . '">' . $tgrep_quick_query_ht . '</a>' . "\n";
            echo '<small>[<a href="tgrepctl.php?file=quick&amp;query=' . $tgrep_quick_query_en . '&amp;purge=true">��</a>]</small></li>' . "\n";
        }
        echo '</ul>' . "\n";
    } else {
        echo '<p>�i�Ȃ��j</p>' . "\n";
    }
    echo '<form method="get" action="tgrepctl.php">';
    echo '<input type="hidden" name="file" value="quick">';
    echo '<input type="text" name="query" value="">';
    echo '<input type="submit" value="�ǉ�">';
    echo '</form>' . "\n";
    if ($tgrep_quick_list) {
        echo '<p><a href="tgrepctl.php?file=quick&amp;clear=all">�ꔭ������ر</a></p>' . "\n";
    }
}