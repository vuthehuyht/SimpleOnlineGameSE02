<?php

namespace common\models;

use Yii;
use yii\web\NotFoundHttpException;


class Messages extends base\Messages
{
    CONST PAGINATION = 10;

    /**
     * Gửi tin nhắn từ người dùng này tới người dùng khác
     *
     * @param int $from_id - user_id của người gửi
     * @param int $to_id - user_id của người nhận
     * @param stirng $message - tin nhắn
     *
     * @return int|boolean  true nếu lưu vào db thành công, false nếu lưu thất bại,
     * -1 nếu không tìm thấy người nhận hoặc người gửi, 0 nếu nội dung tin nhắn trống
     */
    public static function sendMessage($from_id, $to_id, $message){
        $form = new Messages();

        if (!User::findOne($from_id) || !User::findOne($to_id))
            return -1;

        if (!$message)
            return 0;

        $form->from_id = $from_id;
        $form->to_id = $to_id;
        $form->message_body = $message;

        return $form->save();
    }

    /**
     * Lấy thông tin về 10 tin nhắn trong cuộc trò chuyện theo số trang
     *
     *@param int $to_id user_id cửa người còn lại
     *@param int $page số trang
     *
     *@return array ActiveRecord Object trả về mảng chứa thông tin về cuộc trò chuyện
     *
     *@throws NotFoundHttpException throw lỗi khi không tìm thấy người dùng
     */
    public static function getConversion($to_id, $page){
        $from_id = Yii::$app->user;
        if (!$from_id)
            throw new NotFoundHttpException();
        $from_id = $from_id->identity->getId();

        if (!User::findOne($to_id))
            throw new NotFoundHttpException();

        $messsages = self::find()
            ->where([
                'from_id' => $from_id,
                'to_id' => $to_id,
            ])
            ->orWhere([
                'from_id' => $to_id,
                'to_id' => $from_id,
            ])
            ->orderBy([
                'created_at' => SORT_DESC
            ])
            ->limit(($page-1)*self::PAGINATION .",". self::PAGINATION)
            ->all();

        if ($messsages)
            return $messsages;

        return [];
    }


}