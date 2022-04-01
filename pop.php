<?php



function open_mailbox($user, $password) {
    $mbox = imap_open("{192.168.56.101:110/pop3/novalidate-cert}", $user, $password);

    $mc = imap_check($mbox);
    $range = "1:" . $mc->Nmsgs;

    $response = imap_fetch_overview($mbox, $range);

    // $mailboxes = imap_getmailboxes($mbox, "{192.168.56.101}", '*');


    return $response;
}
