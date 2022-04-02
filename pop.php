<?php



function open_mailbox($user, $password) {
    $mbox = imap_open("{192.168.56.101:110/pop3/novalidate-cert}", $user, $password);


    $inbox = imap_search($mbox, 'ALL');

    rsort($inbox);

    $messages = [];

    foreach ($inbox as $message_number) {
        $header = imap_header($mbox, $message_number);
        $message = imap_body($mbox, $message_number);

        $message_structure = imap_fetchstructure($mbox, $message_number);
        if (!property_exists($message_structure, 'parts')) {
            $message_body = imap_fetchbody($mbox, $message_number, 1);
        } else {
            foreach ($message_structure->parts as $part_number => $part) {
                if ($part->subtype === 'HTML') {
                    $message_body = imap_fetchbody($mbox, $message_number, $part_number + 1);
                }
            }
        }

        $messages[] = [
            'header' => $header,
            'message' => $message,
            'message_body' => $message_body
        ];
    }

    return $messages;
}
