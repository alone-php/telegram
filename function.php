<?php

use AlonePhp\Telegram\Bot;
use AlonePhp\Telegram\Body\Process;

/**
 * @param string $token
 * @param bool   $multi
 * @return Bot
 */
function alone_bot(string $token, bool $multi = true): Bot {
    return Bot::tokenApi($token)->multi($multi);
}

/**
 * Body->post有值 信息是有效的
 * @param array $body 收到的信息
 * @param array $pull_type
 * @return Process
 */
function alone_bot_body(array $body, array $pull_type = []): Process {
    return Bot::bodyApi($body, $pull_type);
}