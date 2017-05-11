<?php
require("../vendor/autoload.php");
use Blx32\keybase;
use Blx32\telegram;

//use Blx32\database;

/**
 * @author Gabriel Moura <g@srmoura.com.br>
 * @copyright 2015-2017 SrMoura
 */
$telegram = new Blx32\telegram\Telegram(array(
    'bot_id' => '346296203:AAH8LcYggg_xGctEdiIcGw8wgp4spmkymJY',
    'bot_name' => 'KeyBase_bot',
    'adms' => array('Gabriel Moura' => '228725728'),
    'folder_log' => '../logs'));
$keybase = new Blx32\keybase\Keybase();
$db = new \Blx32\database\MySQL('localhost', 'srmoura_telegram', 'kwyg1g2LU', 'srmoura_telegram');
$session = new \Blx32\telegram\Session($telegram->bot_name, $telegram->ChatID(), $db);
$Botan = new \Blx32\telegram\Botan('7b790b49-0bed-4e08-9e87-13942b189c1e');
$Botan->track($telegram->getData()["message"]);
/**
 * SETS
 */
$text = $telegram->Text();
$chat_id = $telegram->ChatID();
$user_id = $telegram->UserID();

if ($telegram->command('start') !== false):
    $msg = "Você pode fazer muito com ele: \nassinar, verificar, criptografar, gerar mensagens, assinar código.";
    $telegram->sendMessage(array('chat_id' => $chat_id, 'text' => $msg, 'parse_mode' => 'Markdown'));
    $session->set('user', array('username' => $telegram->Username(), 'user_id' => $telegram->UserID()));
endif;

if ($telegram->command('whois') !== false):
    $sting = $telegram->command('whois');
    $session->set('user', array('username' => $telegram->Username(), 'user_id' => $telegram->UserID()));
    if ($sting != null):

        if ($keybase->code($sting) == "NOT_FOUND"):
            $telegram->sendMessage(array('chat_id' => $chat_id, 'text' => 'Usuário não existente!', 'parse_mode' => 'Markdown'));
        else:

            $telegram->file_write('/tmp/' . $sting . '.jpeg', file_get_contents($keybase->picture($sting)));
            if ($keybase->picture($sting) != null):
                $msg = "Nome: " . $keybase->full_name($sting);
                if ($keybase->location($sting) != null):
                    $msg .= "\nLocalização : " . $keybase->location($sting);
                endif;
                $msg .= "\nFingerprint: " . $keybase->key_fingerprint($sting);
                $enviado = $telegram->sendPhoto(array('chat_id' => $chat_id, 'photo' => $telegram->file('/tmp/' . $sting . '.jpeg'), 'caption' => $msg));

                if ($enviado):unlink('/tmp/' . $sting . '.jpeg');endif;
            else:
                $msg = "Nome: " . $keybase->full_name($sting);
                if ($keybase->biography($sting) != null):$msg .= "\nBio: " . $keybase->biography($sting);endif;
                $msg .= "\nFingerprint: " . $keybase->key_fingerprint($sting);
                $telegram->sendMessage(array('chat_id' => $chat_id, 'text' => $msg, 'parse_mode' => 'Markdown'));
            endif;//Tem ou não avatar

        endif;//User não existente

    else:
        $telegram->sendMessage(array('chat_id' => $chat_id, 'text' => 'Nenhum parametro foi passado!', 'parse_mode' => 'Markdown'));
    endif;//Sem parametro

endif;

if ($telegram->command('asc') !== false):
    $session->set('user', array('username' => $telegram->Username(), 'user_id' => $telegram->UserID()));
    $sting = $telegram->command('asc');
    $a = $keybase->export($sting);

    $telegram->sendMessage(array('chat_id' => $chat_id, 'text' => 'Processando...', 'parse_mode' => 'Markdown'));

    if (substr_count($a, 'SELF-SIGNED PUBLIC KEY NOT FOUND') > 0):
        $telegram->sendMessage(array('chat_id' => $chat_id, 'text' => 'Este usuário não possui chave', 'parse_mode' => 'Markdown'));
    elseif (substr_count($a, '404 - sorry, not found.') > 0):
        $telegram->sendMessage(array('chat_id' => $chat_id, 'text' => 'Comando não válido', 'parse_mode' => 'Markdown'));
    else:
        //Busca e escreve o arquivi
        //   $fp = fopen("/tmp/" . $sting . ".asc", "a+");
        //  $escreve = fwrite($fp, $a);
        //  fclose($fp);

        $telegram->file_write("/tmp/" . $sting . ".asc", $a);

        //Se escrito manda mensagem de processo
        //if ($escreve != null):$telegram->sendMessage(array('chat_id' => $chat_id, 'text' => 'Processando...', 'parse_mode' => 'Markdown'));endif;

        $enviado = $telegram->sendDocument(array('chat_id' => $chat_id, 'document' => $telegram->file('/tmp/' . $sting . '.asc')));
        //Se enviado deleta o arquivo
        if ($enviado):unlink('/tmp/' . $sting . '.asc');endif;
    endif;

endif;

if ($telegram->command('about') !== false):
    $session->set('user', array('username' => $telegram->Username(), 'user_id' => $telegram->UserID()));
    $msg = "KeyBase Bot";
    $msg .= "\nEste bot não é da startup https://a16z.com/";
    $msg .= "\nDesenvolverdor: " . $botan->shortenUrl('https://keybase.io/gabrielmoura', $user_id);
    $msg .= "\nVersão: 0.9";
    $msg .= "\n\nRecomendo o uso do APG[https://goo.gl/wbWg9u], com ele é possivel encriptar e verificar assinaturas.";
    $telegram->sendMessage(array('chat_id' => $chat_id, 'text' => $msg, 'parse_mode' => 'Markdown'));
endif;

if ($telegram->command('test') !== false):
    $session->set('user', array('username' => $telegram->Username(), 'user_id' => $telegram->UserID()));
    $telegram->sendMessage(array('chat_id' => $chat_id, 'text' => 'O comando foi: ' . $telegram->command('test', $text), 'parse_mode' => 'Markdown'));
endif;
