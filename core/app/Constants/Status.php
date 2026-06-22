<?php

namespace App\Constants;

class Status
{

    const ENABLE  = 1;
    const DISABLE = 0;

    const YES = 1;
    const NO  = 0;

    const VERIFIED   = 1;
    const UNVERIFIED = 0;

    const PAYMENT_INITIATE = 0;
    const PAYMENT_SUCCESS  = 1;
    const PAYMENT_PENDING  = 2;
    const PAYMENT_REJECT   = 3;

    const TICKET_OPEN   = 0;
    const TICKET_ANSWER = 1;
    const TICKET_REPLY  = 2;
    const TICKET_CLOSE  = 3;

    const PRIORITY_LOW    = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH   = 3;

    const USER_ACTIVE = 1;
    const USER_BAN    = 0;

    const KYC_UNVERIFIED = 0;
    const KYC_PENDING    = 2;
    const KYC_VERIFIED   = 1;

    const GOOGLE_PAY = 5001;

    const CUR_BOTH = 1;
    const CUR_TEXT = 2;
    const CUR_SYM  = 3;

    const SINGLE_ITEM  = 1;
    const EPISODE_ITEM = 2;

    const FREE_VERSION = 0;
    const PAID_VERSION = 1;
    const RENT_VERSION = 2;

    const TRAILER = 1;

    const CURRENT_SERVER       = 0;
    const FTP_SERVER           = 1;
    const WASABI_SERVER        = 2;
    const DIGITAL_OCEAN_SERVER = 3;

    const WATCH_PARTY_REQUEST_PENDING  = 0;
    const WATCH_PARTY_REQUEST_ACCEPTED = 1;
    const WATCH_PARTY_REQUEST_REJECTED = 2;

    const FORMAT_SKIPABLE = 1;
    const FORMAT_NONSKIPABLE = 2;

    const REQUEST_ITEM_PENDING = 0;
    const REQUEST_ITEM_ACCEPTED = 1;
    const REQUEST_ITEM_REJECTED = 2;

    const VOTE_UP = 1;
    const VOTE_DOWN = 2;
}
