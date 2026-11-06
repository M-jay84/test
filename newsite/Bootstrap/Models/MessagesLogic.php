<?php
declare(strict_types=1);

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\TTMail;
use PDO;

class MessagesLogic
{
    public static function newinsert(array $data): void
    {
        DB::insert('messages', $data);

        $user = DB::run(
            'SELECT id, username, acceptpms, notifs, email FROM users WHERE id = ?',
            [(int) ($data['receiver'] ?? 0)]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return;
        }

        if (str_contains((string) ($user['notifs'] ?? ''), '[pm]')) {
            $link = url('message/mailbox/inbox');
            $body = "You have received a PM\n\n"
                . "You can use the URL below to view the message (you may have to login).\n\n"
                . $link . "\n\n"
                . (CONFIG['SITENAME'] ?? '');

            $mail = new TTMail();
            $mail->Send(
                (string) $user['email'],
                'You have received a PM',
                $body,
                'From: ' . CONFIG['SITEEMAIL'],
                '-f' . CONFIG['SITEEMAIL']
            );
        }
    }

    public static function msgPagination(string $type, int $id): array
    {
        if ($type === 'outbox') {
            $where  = 'sender = ? AND location IN (\'out\',\'both\')';
            $pageUrl = url('message/mailbox/outbox?');
        } else {
            $type   = 'inbox';
            $where  = 'receiver = ? AND location IN (\'in\',\'both\')';
            $pageUrl = url('message/mailbox/inbox?');
        }

        $count = (int) DB::run(
            "SELECT COUNT(*) FROM messages WHERE $where",
            [$id]
        )->fetchColumn();

        [$pager, $limit] = pagination(25, $count, $pageUrl);

        $messages = DB::run(
            "SELECT * FROM messages WHERE $where ORDER BY added DESC $limit",
            [$id]
        )->fetchAll(PDO::FETCH_ASSOC);

        return ['pager' => $pager, 'messages' => $messages];
    }

    public static function insertByType(int $senderId): never
    {
        $receiver = input_string('receiver');
        $subject  = input_string('subject');
        $body     = input_string('body');
        $history  = input_int('history', 0);
        $update   = input_string('Update') !== '' ? input_string('Update') : (string) ($_REQUEST['Update'] ?? 'create');

        if (strlen($body) < 5 || strlen($subject) < 3) {
            redirect('message/mailbox/inbox', __('SMALL_INPUT'), 'warning');
        }

        if (!ctype_digit($receiver)) {
            $receiver = (string) (int) DB::run(
                'SELECT id FROM users WHERE username = ?',
                [$receiver]
            )->fetchColumn();
        }

        $receiverId = (int) $receiver;

        if ($receiverId < 1 && $update === 'create') {
            redirect('message/create', __('MISSING_ID'), 'warning');
        }

        switch ($update) {
            case 'create':
                $location = isset($_POST['save']) ? 'both' : 'in';
                self::newinsert([
                    'sender'   => $senderId,
                    'receiver' => $receiverId,
                    'added'    => CONFIG['TT_DATE'],
                    'subject'  => $subject,
                    'msg'      => $body,
                    'unread'   => 'yes',
                    'location' => $location,
                    'history'  => $history,
                ]);
                redirect('message/mailbox/outbox', __('MESSAGES_SENT'), 'success');

            case 'draft':
                self::newinsert([
                    'sender'   => $senderId,
                    'receiver' => $receiverId,
                    'added'    => CONFIG['TT_DATE'],
                    'subject'  => $subject,
                    'msg'      => $body,
                    'unread'   => 'no',
                    'location' => 'draft',
                ]);
                redirect('message/mailbox/draft', __('SAVED_DRAFT'), 'success');

            case 'template':
                self::newinsert([
                    'sender'   => $senderId,
                    'receiver' => $receiverId,
                    'added'    => CONFIG['TT_DATE'],
                    'subject'  => $subject,
                    'msg'      => $body,
                    'unread'   => 'no',
                    'location' => 'template',
                ]);
                redirect('message/mailbox/templates', __('SAVED_TEMPLATE'), 'success');

            default:
                redirect('message/create', __('MISSING_FORM_DATA'), 'warning');
        }
    }

    /** @param string $type inbox|outbox */
    public static function delete(string $type, int $userid): never
    {
        $raw = multiIds('del');
        $ids = is_array($raw)
            ? implode(',', array_map('intval', $raw))
            : preg_replace('/[^0-9,]/', '', (string) $raw);

        if ($ids === '' || $ids === '0') {
            redirect('message/mailbox/' . $type, __('NOTHING_SELECTED'), 'warning');
        }

        if (!empty($_POST['read'])) {
            DB::run(
                "UPDATE messages SET unread = ? WHERE id IN ($ids) AND receiver = ?",
                ['no', $userid]
            );
            redirect('message/mailbox/' . $type, __('SUCCESS'), 'success');
        }

        if ($type === 'inbox') {
            DB::run(
                "DELETE FROM messages WHERE location = ? AND receiver = ? AND id IN ($ids)",
                ['in', $userid]
            );
            DB::run(
                "UPDATE messages SET location = ? WHERE location = ? AND receiver = ? AND id IN ($ids)",
                ['out', 'both', $userid]
            );
        } elseif ($type === 'outbox') {
            DB::run(
                "UPDATE messages SET location = ? WHERE location = ? AND sender = ? AND id IN ($ids)",
                ['in', 'both', $userid]
            );
            DB::run(
                "DELETE FROM messages WHERE location IN ('out','draft','template') AND sender = ? AND id IN ($ids)",
                [$userid]
            );
        }

        redirect('message/mailbox/' . $type, __('SUCCESS'), 'success');
    }

    public static function getById(int $id, int $userid): array
    {
        $arr = DB::run(
            'SELECT * FROM messages WHERE id = ?',
            [$id]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$arr) {
            redirect('message/mailbox/inbox', __('MISSING_ID'), 'warning');
        }

        if ((int) $arr['sender'] !== $userid && (int) $arr['receiver'] !== $userid) {
            redirect('home', __('NO_PERMISSION'), 'warning');
        }

        $history = [];
        if (!empty($arr['history'])) {
            $history = DB::run(
                'SELECT * FROM messages WHERE history = ? ORDER BY added ASC',
                [(int) $arr['history']]
            )->fetchAll(PDO::FETCH_ASSOC);
        }

        if (($arr['unread'] ?? '') === 'yes' && (int) $arr['receiver'] === $userid) {
            DB::run(
                'UPDATE messages SET unread = ? WHERE id = ? AND receiver = ?',
                ['no', $id, $userid]
            );
            $arr['unread'] = 'no';
        }

        $arr['replyto']  = ((int) $arr['receiver'] === $userid)
            ? (int) $arr['sender']
            : (int) $arr['receiver'];
        $arr['previous'] = $history;

        return $arr;
    }

    public static function update(int $id, int $userid): never
    {
        $row = DB::run(
            'SELECT * FROM messages WHERE id = ?',
            [$id]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            redirect('message/mailbox/inbox', __('MISSING_ID'), 'warning');
        }

        if ((int) $row['sender'] !== $userid && (int) $row['receiver'] !== $userid) {
            redirect('home', __('NO_PERMISSION'), 'warning');
        }

        $msg = input_string('msg');
        if ($msg === '') {
            redirect('message/view/' . $id, __('EDIT_FAILED'), 'warning');
        }

        DB::run('UPDATE messages SET msg = ? WHERE id = ?', [$msg, $id]);
        redirect('message/view/' . $id, __('UPDATE_COMPLETE'), 'success');
    }

    public static function deleteByIds(): never
    {
        $raw = multiIds('ids');
        $ids = is_array($raw)
            ? implode(',', array_map('intval', $raw))
            : preg_replace('/[^0-9,]/', '', (string) $raw);

        if ($ids === '' || $ids === '0') {
            redirect('message/admin', __('NOTHING_SELECTED'), 'warning');
        }

        DB::run("DELETE FROM messages WHERE id IN ($ids)");
        redirect('message/admin', __('DEL_COMPLETE'), 'success');
    }

    public static function multiPM(int $staffId): never
    {
        $msg     = input_string('msg');
        $subject = input_string('subject');
        $sender  = (input_string('sender') === 'system') ? 0 : $staffId;

        if ($msg === '') {
            redirect('message/admin', __('MISSING_FORM_DATA'), 'warning');
        }

        $classes = array_map('intval', (array) ($_POST['classes'] ?? []));
        $classes = array_filter($classes, static fn (int $c): bool => $c > 0);

        if ($classes === []) {
            redirect('message/admin', __('NOTHING_SELECTED'), 'warning');
        }

        $in    = implode(',', $classes);
        $users = DB::run("SELECT id FROM users WHERE class IN ($in)")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($users as $uid) {
            DB::run(
                'INSERT INTO messages (sender, receiver, added, msg, subject, unread, location)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$sender, (int) $uid, CONFIG['TT_DATE'], $msg, $subject, 'yes', 'in']
            );
        }

        LogsLogic::write('A Mass PM was sent by user id ' . $staffId);
        redirect('message/admin', __('SUCCESS'), 'success');
    }

    public static function getAll(): array
    {
        $count = (int) DB::run(
            "SELECT COUNT(*) FROM messages WHERE location IN ('in','both')"
        )->fetchColumn();

        [$pager, $limit] = pagination(40, $count, url('message/admin?'));

        $stmt = DB::run(
            "SELECT * FROM messages WHERE location IN ('in','both') ORDER BY id DESC $limit"
        )->fetchAll(PDO::FETCH_ASSOC);

        $groups = DB::run(
            'SELECT group_id, level FROM groups ORDER BY group_id'
        )->fetchAll(PDO::FETCH_ASSOC);

        return ['res' => $stmt, 'pager' => $pager, 'groups' => $groups];
    }
}