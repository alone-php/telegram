<?php

namespace AlonePhp\Telegram\Bot;

trait Command{
    /**
     * 设置机器人命令
     * 使用此方法更改机器人的命令列表。有关机器人命令的更多详细信息，请参阅本手册。成功后返回真实。
     * https://core.telegram.org/bots/api#setmycommands
     * @param string|array $command 为空删除命令
     * @param string       $content
     * @return $this
     */
    public function setMyCommands(string|array $command = [], string $content = ''): static {
        $commands = [];
        if (!empty($command)) {
            if (is_array($command)) {
                foreach ($command as $key => $value) {
                    $commands[] = ['command' => $key, 'description' => $value];
                }
            } else {
                $commands = [['command' => $command, 'description' => $content ?: $command]];
            }
        }
        return $this->curl('setMyCommands', json_encode(['commands' => $commands]), 'json');
    }

    /**
     * 获取命令
     * 使用此方法获取给定范围和用户语言的机器人命令的当前列表。返回BotCommand对象数组。如果未设置命令，则返回一个空列表。
     * https://core.telegram.org/bots/api#getmycommands
     * @return $this
     */
    public function getMyCommands(): static {
        return $this->curl('getMyCommands');
    }

    /**
     * 使用此方法在私人聊天中更改机器人的菜单按钮或默认菜单按钮。成功时返回True
     * https://core.telegram.org/bots/api#setchatmenubutton
     * @param string     $type default commands web_app
     * @param string     $text
     * @param string     $url
     * @param string|int $chat_id
     * @return $this
     */
    public function setChatMenuButton(string $type = 'default', string $text = '', string $url = '', string|int $chat_id = ''): static {
        if ($chat_id) {
            $data['chat_id'] = $chat_id;
        }
        $data['menu_button'] = $type == 'web_app' ? ['type' => $type, 'text' => $text, 'web_app' => ['url' => $url]] : ['type' => $type];
        return $this->curl('setChatMenuButton', json_encode($data), 'json');
    }

    /**
     * 使用此方法在私人聊天中获取机器人菜单按钮的当前值，或默认菜单按钮。成功时返回菜单按钮。
     * https://core.telegram.org/bots/api#getChatMenuButton
     * @param string|int $chat_id
     * @return $this
     */
    public function getChatMenuButton(string|int $chat_id = ''): static {
        if ($chat_id) {
            $data['chat_id'] = $chat_id;
        }
        return $this->curl('getChatMenuButton', $data ?? []);
    }
}