<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\AmazonSES;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function test () {
        // single email
        $data = array(
            'target_email' => array(
                array(
                    'name' => 'MEMBER',
                    'email' => 'aa1010169@gmail.com'
                )
            ),
            'subject' => 'AEGIS 인증 코드',
            'content' => 'AEGIS 인증 코드는 '
        );
        // multi email
        /*$data = array(
            'target_email' => array(
                array(
                    'name' => '네이버잡스',
                    'email' => 'sjwiq200@naver.com'
                ),
                array(
                    'name' => '지메일잡스',
                    'email' => 'sjwiq200@gmail.com'
                )
            ),
            'subject' => '재우 짱짱맨',
            'content' => '안녕하세요'
        );*/
        return $this->sendMail($data);
    }

    /**
     * @param $options
     * $options['target_email']
     * $options['subject']
     * $options['content']
     * @return string
     */
    public static function sendMail($options)
    {
        Mail::to($options['target_email'])->send(new AmazonSES(array(
            'subject' => $options['subject'],
            'content' => $options['content']
        )));
    }
}
