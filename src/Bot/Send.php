<?php

namespace AlonePhp\Telegram\Bot;

trait Send {
    /**
     * 发送付费媒体
     * 使用此方法向频道聊天发送付费媒体。成功后，将返回已发送的消息。
     * https://core.telegram.org/bots/api#sendpaidmedia
     * @param int|float $star_count 要支付的数量
     * @param array     $media      文件二维array  参数说明 https://core.telegram.org/bots/api#inputpaidmedia
     * @param string    $caption    内容
     * @param array     $conf       配置
     * @return $this result.paid_media.key
     */
    public function sendPaidMedia(int|float $star_count, array $media, string $caption = '', array $conf = []): static {
        $conf['chat_id'] = $this->chat_id;
        $conf['star_count'] = $star_count;
        $conf['media'] = $media;
        $conf['caption'] = $caption;
        $data = $this->get_conf([
            'chat_id',
            'star_count',
            'media',
            'caption',
            'parse_mode' => 'HTML',
            'caption_entities',
            'show_caption_above_media',
            'disable_notification',
            'protect_content',
            'reply_parameters',
            'reply_markup'
        ], $this->reply_markup($conf));
        return $this->curl('sendPaidMedia', json_encode($data), 'json');
    }

    /**
     * 发送付费媒体成功后获取file_id
     * @param array $arr
     * @return array
     */
    public function getPaidMediaFileId(array $arr = []): array {
        if (!empty($this->array('ok')) && !empty($paid_media = $this->array('result.paid_media.paid_media'))) {
            foreach ($paid_media as $key => $value) {
                $type = static::getArr($value, 'type');
                if (!empty($type)) {
                    $list = static::getArr($value, $type);
                    if (!empty($list)) {
                        $unique_id = [];
                        foreach ($list as $k => $v) {
                            if (!empty($file_unique_id = ($v['file_unique_id'] ?? ''))) {
                                $unique_id[$key][$k] = $file_unique_id;
                            }
                            $file_id = ($v['file_id'] ?? '');
                        }
                        if (!empty($file_id)) {
                            $arr[$key] = ['file_id' => $file_id, 'unique_id' => $unique_id];
                        }
                    }
                }
            }
        }
        return $arr;
    }

    /**
     * 发送游戏
     * 使用此方法发送游戏。成功后，将返回发送的消息。
     * https://core.telegram.org/bots/api#sendgame
     * @param string $name 游戏标识
     * @param array  $conf
     * @return $this
     */
    public function sendGame(string $name, array $conf = []): static {
        $conf['chat_id'] = $this->chat_id;
        $conf['game_short_name'] = $name;
        $data = $this->get_conf([
            'business_connection_id',
            'chat_id',
            'message_thread_id',
            'game_short_name',
            'disable_notification',
            'protect_content',
            'message_effect_id',
            'reply_parameters',
            'reply_markup'
        ], $this->reply_markup($conf));
        return $this->curl('sendGame', $data);
    }

    /**
     * 发送文本
     * 使用此方法发送短信。成功后，将返回发送的消息。
     * https://core.telegram.org/bots/api#sendmessage
     * @param string $content 发送内容
     * @param array  $conf    发送配置
     * @return $this
     */
    public function sendMessage(string $content, array $conf = []): static {
        $conf['chat_id'] = $this->chat_id;
        $conf['text'] = $content;
        $data = $this->get_conf([
            'business_connection_id',//将代表其发送消息的业务连接的唯一标识符
            'chat_id',//目标频道的目标聊天或用户名的唯一标识符（格式为@channelusername）
            'message_thread_id',//论坛目标消息线程（主题）的唯一标识符；仅适用于论坛超级组
            'text',//要发送的消息文本，实体解析后1-4096个字符
            'parse_mode'      => 'HTML',//解析消息文本中实体的模式。有关更多详细信息，请参阅格式选项。(MarkdownV2|HTML|Markdown)
            'entities',//消息文本中出现的特殊实体的JSON序列化列表，可以指定而不是parse_mode
            'link_preview_options',//消息的链接预览生成选项
            'disable_notification',//默默地发送消息。用户将收到没有声音的通知。
            'protect_content' => false,//保护发送消息的内容免受转发和保存
            'message_effect_id',//要添加到消息中的消息效果的唯一标识符；仅适用于私人聊天
            'reply_parameters',//要回复的消息的描述
            'reply_markup'//额外的界面选项。内联键盘的JSON序列化对象、自定义回复键盘、删除回复键盘或强制用户回复的说明
        ], $this->reply_markup($conf));
        return $this->curl('sendMessage', $data);
    }

    /**
     * 发送信息删除键盘
     * @param string $content
     * @param bool   $selective false表示对所有用户生效，true表示只对特定用户生效
     * @return $this
     */
    public function deleteMessageReplyMarkup(string $content, bool $selective = false): static {
        $conf = [
            'chat_id'      => $this->chat_id,
            'text'         => $content,
            'reply_markup' => json_encode([
                'remove_keyboard' => true,
                'selective'       => $selective
            ])
        ];
        return $this->curl('editMessageReplyMarkup', $conf);
    }

    /**
     * 发送图片+文本
     * 使用此方法发送照片。成功后，将返回已发送的消息。
     * https://core.telegram.org/bots/api#sendphoto
     * @param mixed  $file    文件
     * @param string $content 发送内容
     * @param array  $conf    发送配置
     * @return $this
     */
    public function sendPhoto(mixed $file, string $content = '', array $conf = []): static {
        $conf['chat_id'] = $this->chat_id;
        $conf['photo'] = $file;
        $conf['caption'] = $content;
        $data = $this->get_conf([
            'business_connection_id',
            'chat_id',
            'message_thread_id',
            'photo',
            'caption',
            'parse_mode' => 'HTML',
            'caption_entities',
            'show_caption_above_media',
            'has_spoiler',
            'disable_notification',
            'protect_content',
            'message_effect_id',
            'reply_parameters',
            'reply_markup'
        ], $this->reply_markup($conf));
        return $this->curl('sendPhoto', $data);
    }

    /**
     * 发送动画+文本
     * 使用此方法发送动画文件（GIF或没有声音的H.264/MPEG-4 AVC视频）。成功后，将返回已发送的消息。机器人目前可以发送大小高达50 MB的动画文件，此限制将来可能会更改。
     * https://core.telegram.org/bots/api#sendanimation
     * @param mixed  $file    文件
     * @param string $content 发送内容
     * @param array  $conf    发送配置
     * @return $this
     */
    public function sendAnimation(mixed $file, string $content = '', array $conf = []): static {
        $conf['chat_id'] = $this->chat_id;
        $conf['animation'] = $file;
        $conf['caption'] = $content;
        $data = $this->get_conf([
            'business_connection_id',
            'chat_id',
            'message_thread_id',
            'animation',
            'duration',
            'width',
            'height',
            'thumbnail',
            'caption',
            'parse_mode' => 'HTML',
            'caption_entities',
            'show_caption_above_media',
            'has_spoiler',
            'disable_notification',
            'protect_content',
            'message_effect_id',
            'reply_parameters',
            'reply_markup',
        ], $this->reply_markup($conf));
        return $this->curl('sendAnimation', $data);
    }

    /**
     * 发送视频+文本
     * 使用此方法发送视频文件。成功后，将返回已发送的消息。机器人目前可以发送大小高达50 MB的视频文件，这个限制将来可能会改变。
     * https://core.telegram.org/bots/api#sendvideo
     * @param mixed  $file    文件
     * @param string $content 发送内容
     * @param array  $conf    发送配置
     * @return $this
     */
    public function sendVideo(mixed $file, string $content = '', array $conf = []): static {
        $conf['chat_id'] = $this->chat_id;
        $conf['video'] = $file;
        $conf['caption'] = $content;
        $data = $this->get_conf([
            'business_connection_id',
            'chat_id',
            'message_thread_id',
            'video',
            'caption',
            'duration',
            'width',
            'height',
            'thumbnail',
            'parse_mode' => 'HTML',
            'caption_entities',
            'show_caption_above_media',
            'has_spoiler',
            'supports_streaming',
            'disable_notification',
            'protect_content',
            'message_effect_id',
            'reply_parameters',
            'reply_markup'
        ], $this->reply_markup($conf));
        return $this->curl('sendVideo', $data);
    }

    /**
     * 发送音频+文本
     * 请使用此方法发送音频文件。您的音频必须在.MP3或。M4A格式。成功后，将返回已发送的消息。机器人目前可以发送大小高达50 MB的音频文件，这一限制将来可能会发生变化。
     * https://core.telegram.org/bots/api#sendaudio
     * @param mixed  $file    文件
     * @param string $content 发送内容
     * @param array  $conf    发送配置
     * @return $this
     */
    public function sendAudio(mixed $file, string $content = '', array $conf = []): static {
        $conf['chat_id'] = $this->chat_id;
        $conf['audio'] = $file;
        $conf['caption'] = $content;
        $data = $this->get_conf([
            'business_connection_id',
            'chat_id',
            'message_thread_id',
            'audio',
            'caption',
            'parse_mode' => 'HTML',
            'caption_entities',
            'duration',
            'performer',
            'title',
            'thumbnail',
            'disable_notification',
            'protect_content',
            'message_effect_id',
            'reply_parameters',
            'reply_markup'
        ], $this->reply_markup($conf));
        return $this->curl('sendAudio', $data);
    }

    /**
     * 发送语音+文本
     * 请使用此方法发送音频文件。要让这个工作，你的音频必须在一个。用OPUS编码的OGG文件，或在。MP3格式，或。M4A格式（其他格式可以作为音频或文档发送）。成功后，将返回已发送的消息。机器人目前可以发送大小高达50 MB的语音消息，此限制将来可能会更改。
     * https://core.telegram.org/bots/api#sendvoice
     * @param mixed  $file    文件
     * @param string $content 发送内容
     * @param array  $conf    发送配置
     * @return $this
     */
    public function sendVoice(mixed $file, string $content = '', array $conf = []): static {
        $conf['chat_id'] = $this->chat_id;
        $conf['voice'] = $file;
        $conf['caption'] = $content;
        $data = $this->get_conf([
            'business_connection_id',
            'chat_id',
            'message_thread_id',
            'voice',
            'caption',
            'parse_mode' => 'HTML',
            'caption_entities',
            'duration',
            'disable_notification',
            'protect_content',
            'message_effect_id',
            'reply_parameters',
            'reply_markup'
        ], $this->reply_markup($conf));
        return $this->curl('sendVoice', $data);
    }

    /**
     * 发送文档+文本
     * 使用此方法发送常规文件。成功后，将返回已发送的消息。机器人目前可以发送任何类型的文件，大小不超过50 MB，此限制将来可能会更改。
     * https://core.telegram.org/bots/api#senddocument
     * @param mixed  $file    文件
     * @param string $content 发送内容
     * @param array  $conf    发送配置
     * @return $this
     */
    public function sendDocument(mixed $file, string $content = '', array $conf = []): static {
        $conf['chat_id'] = $this->chat_id;
        $conf['document'] = $file;
        $conf['caption'] = $content;
        $data = $this->get_conf([
            'business_connection_id',
            'chat_id',
            'message_thread_id',
            'document',
            'thumbnail',
            'caption',
            'parse_mode' => 'HTML',
            'caption_entities',
            'disable_content_type_detection',
            'disable_notification',
            'protect_content',
            'message_effect_id',
            'reply_parameters',
            'reply_markup'
        ], $this->reply_markup($conf));
        return $this->curl('sendDocument', $data);
    }

    /**
     * 发起投票
     * https://core.telegram.org/bots/api#sendpoll
     * @param string $title     投票名称
     * @param array  $options   投票内容
     * @param bool   $anonymous 设置为 true 以匿名投票
     * @param string $type      投票类型，可以是 'regular' 或 'quiz'
     * @param array  $conf
     * @return $this 发判成功可以保存result.poll.id
     */
    public function sendPoll(string $title, array $options, bool $anonymous = true, string $type = 'regular', array $conf = []): static {
        $conf['chat_id'] = $this->chat_id;
        $conf['question'] = $title;
        $conf['options'] = json_encode($options);
        $conf['is_anonymous'] = $anonymous;
        $conf['type'] = $type;
        $data = $this->get_conf([
            'business_connection_id',
            'chat_id',
            'message_thread_id',
            'question',
            'question_parse_mode',
            'question_entities',
            'options',
            'is_anonymous',
            'type',
            'allows_multiple_answers',
            'correct_option_id',
            'explanation',
            'explanation_parse_mode',
            'explanation_entities',
            'open_period',
            'close_date',
            'is_closed',
            'disable_notification',
            'protect_content',
            'message_effect_id',
            'reply_parameters',
            'reply_markup',
        ], $this->reply_markup($conf));
        return $this->curl('sendPoll', $data);
    }

    /**
     * 发送骰子
     * 使用此方法发送一个动画表情符号，该表情符号将显示随机值。成功后，将返回已发送的消息。
     * https://core.telegram.org/bots/api#senddice
     * @param int   $emoji
     * @param array $conf
     * @return $this result.dice.value是结果值
     */
    public function sendDice(int $emoji = 0, array $conf = []): static {
        $conf['chat_id'] = $this->chat_id;
        $emo = [
            1 => '🎲',//1-6
            2 => '🎯',//1-6
            3 => '🎳',//1-6
            4 => '🏀',//1-5
            5 => '⚽',//1-5
            6 => '🎰',//1-5
        ];
        $conf['emoji'] = $emo[$emoji] ?? 1;
        $data = $this->get_conf([
            'business_connection_id',
            'chat_id',
            'message_thread_id',
            'emoji',
            'disable_notification',
            'protect_content',
            'message_effect_id',
            'reply_parameters',
            'reply_markup'
        ], $this->reply_markup($conf));
        return $this->curl('sendDice', $data);
    }
}