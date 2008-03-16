<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */

require_once P2EX_LIBRARY_DIR . '/ic2/database.class.php';
require_once P2EX_LIBRARY_DIR . '/ic2/db_images.class.php';
require_once P2EX_LIBRARY_DIR . '/ic2/db_blacklist.class.php';
require_once P2EX_LIBRARY_DIR . '/ic2/thumbnail.class.php';

// TODO: �����̌��؂ƃg�����U�N�V�����̊J�n/�I������ɂ܂Ƃ߂�

/**
 * �摜�����X�V
 */
function manageDB_update($updated)
{
    if (empty($updated)) {
        return;
    }
    if (!is_array($updated)) {
        global $_info_msg_ht;
        $_info_msg_ht .= '<p>WARNING! manageDB_update(): �s���Ȉ���</p>';
        return;
    }

    // �g�����U�N�V�����̊J�n
    $ta = &new IC2DB_Images;
    if ($ta->_db->phptype == 'pgsql') {
        $ta->query('BEGIN');
    } elseif ($ta->_db->phptype == 'sqlite') {
        $ta->_db->query('BEGIN;');
    }

    // �摜�f�[�^���X�V
    foreach ($updated as $id => $data) {
        $icdb = &new IC2DB_Images;
        $icdb->whereAdd("id = $id");
        if ($icdb->find(TRUE)) {
            // �������X�V
            if ($icdb->memo != $data['memo']) {
                $memo = &new IC2DB_Images;
                $memo->memo = (strlen($data['memo']) > 0) ? $data['memo'] : '';
                $memo->whereAdd("id = $id");
                $memo->update();
            }
            // �����N���X�V
            if ($icdb->rank != $data['rank']) {
                $rank = &new IC2DB_Images;
                $rank->rank = $data['rank'];
                $rank->whereAddQuoted('size', '=', $icdb->size);
                $rank->whereAddQuoted('md5',  '=', $icdb->md5);
                $rank->whereAddQuoted('mime', '=', $icdb->mime);
                $rank->update();
            }
        }
    }

    // �g�����U�N�V�����̃R�~�b�g
    if ($ta->_db->phptype == 'pgsql') {
        $ta->query('COMMIT');
    } elseif ($ta->_db->phptype == 'sqlite') {
        $ta->_db->query('COMMIT;');
    }
}

/**
 * �摜���폜
 */
function manageDB_remove($target, $to_blacklist = FALSE)
{
    $removed_files = array();
    if (empty($target)) {
        return $removed_files;
    }
    if (!is_array($target)) {
        if (is_integer($target) || ctype_digit($target)) {
            $id = (int) $target;
            if ($id > 0) {
                $target = array($id);
            } else {
                return $removed_files;
            }
        } else {
            global $_info_msg_ht;
            $_info_msg_ht .= '<p>WARNING! manageDB_remove(): �s���Ȉ���</p>';
            return $removed_files;
        }
    }

    // �g�����U�N�V�����̊J�n
    $ta = &new IC2DB_Images;
    if ($ta->_db->phptype == 'pgsql') {
        $ta->query('BEGIN');
    } elseif ($ta->_db->phptype == 'sqlite') {
        $ta->_db->query('BEGIN;');
    }

    // �摜���폜
    foreach ($target as $id) {
        $icdb = &new IC2DB_Images;
        $icdb->whereAdd("id = $id");

        if ($icdb->find(TRUE)) {
            // �L���b�V�����Ă���t�@�C�����폜
            $t1 = &new ThumbNailer(1);
            $t2 = &new ThumbNailer(2);
            $t3 = &new ThumbNailer(3);
            $srcPath = $t1->srcPath($icdb->size, $icdb->md5, $icdb->mime);
            $t1Path = $t1->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
            $t2Path = $t2->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
            $t3Path = $t3->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
            if (file_exists($srcPath)) {
                unlink($srcPath);
                $removed_files[] = $srcPath;
            }
            if (file_exists($t1Path)) {
                unlink($t1Path);
                $removed_files[] = $t1Path;
            }
            if (file_exists($t2Path)) {
                unlink($t2Path);
                $removed_files[] = $t2Path;
            }
            if (file_exists($t3Path)) {
                unlink($t3Path);
                $removed_files[] = $t3Path;
            }

            // �u���b�N���X�g����̏���
            if ($to_blacklist) {
                $_blacklist = &new IC2DB_BlackList;
                $_blacklist->size = $icdb->size;
                $_blacklist->md5  = $icdb->md5;
                if ($icdb->mime == 'clamscan/infected' || $icdb->rank == -4) {
                    $_blacklist->type = 2;
                } elseif ($icdb->rank < 0) {
                    $_blacklist->type = 1;
                } else {
                    $_blacklist->type = 0;
                }
            }

            // ����摜������
            $remover = &new IC2DB_Images;
            $remover->whereAddQuoted('size', '=', $icdb->size);
            $remover->whereAddQuoted('md5',  '=', $icdb->md5);
            //$remover->whereAddQuoted('mime', '=', $icdb->mime); // Size��MD5�ŏ\��
            $remover->find();
            while ($remover->fetch()) {
                // �u���b�N���X�g����ɂ���
                if ($to_blacklist) {
                    $blacklist = clone($_blacklist);
                    $blacklist->uri = $remover->uri;
                    $blacklist->insert();
                }
                // �e�[�u�����疕��
                $remover->delete();
            }
        }
    }

    // �g�����U�N�V�����̃R�~�b�g
    if ($ta->_db->phptype == 'pgsql') {
        $ta->query('COMMIT');
    } elseif ($ta->_db->phptype == 'sqlite') {
        $ta->_db->query('COMMIT;');
    }

    return $removed_files;
}

/**
 * �����N��ݒ�
 */
function manageDB_setRank($target, $rank)
{
    if (empty($target)) {
        return;
    }
    if (!is_array($target)) {
        if (is_integer($updated) || ctype_digit($updated)) {
            $id = (int)$updated;
            if ($id > 0) {
                $updated = array($id);
            } else {
                return;
            }
        } else {
            global $_info_msg_ht;
            $_info_msg_ht .= '<p>WARNING! manageDB_setRank(): �s���Ȉ���</p>';
            return $removed_files;
        }
    }

    $icdb = &new IC2DB_Images;
    $icdb->rank = $rank;
    foreach ($target as $id) {
        $icdb->whereAdd("id = $id", 'OR');
    }
    $icdb->update();
}

/**
 * ������ǉ�
 */
function manageDB_addMemo($target, $memo)
{
    if (empty($target)) {
        return;
    }
    if (!is_array($target)) {
        if (is_integer($updated) || ctype_digit($updated)) {
            $id = (int)$updated;
            if ($id > 0) {
                $updated = array($id);
            } else {
                return;
            }
        } else {
            global $_info_msg_ht;
            $_info_msg_ht .= '<p>WARNING! manageDB_addMemo(): �s���Ȉ���</p>';
            return $removed_files;
        }
    }

    // �g�����U�N�V�����̊J�n
    $ta = &new IC2DB_Images;
    if ($ta->_db->phptype == 'pgsql') {
        $ta->query('BEGIN');
    } elseif ($ta->_db->phptype == 'sqlite') {
        $ta->_db->query('BEGIN;');
    }

    // �����Ɏw�蕶���񂪊܂܂�Ă��Ȃ���΍X�V
    foreach ($target as $id) {
        $find = &new IC2DB_Images;
        $find->whereAdd("id = $id");
        if ($find->find(TRUE) && !strstr($find->memo, $memo)) {
            $update = &new IC2DB_Images;
            $update->whereAdd("id = $id");
            if (strlen($find->memo) > 0) {
                $update->memo = $find->memo . ' ' . $memo;
            } else {
                $update->memo = $memo;
            }
            $update->update();
            unset($update);
        }
        unset($find);
    }

    // �g�����U�N�V�����̃R�~�b�g
    if ($ta->_db->phptype == 'pgsql') {
        $ta->query('COMMIT');
    } elseif ($ta->_db->phptype == 'sqlite') {
        $ta->_db->query('COMMIT;');
    }
}