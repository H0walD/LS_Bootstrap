<?php
/**
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License (GPL)
 * @copyright (C) 2000-2012 ilch.de
 */
defined('main') or die('no direct access');

/**
 * PwCrypt
 * 
 * Achtung: beim �bertragen von mit 2a erzeugten Passw�rtern auf einen anderen PC/Server,
 * dort kann es u.U. passieren, dass eine Authentifikation nicht mehr m�glich ist,
 * da 2a auf einigen System fehlerhafte Ergebnisse liefert.
 * Verwende dann die $backup Parameter bei checkPasswd
 *
 * @author finke <Surf-finke@gmx.de>
 * @autor Mairu
 * @copyright Copyright (c) 2012 - 2013
 */
class PwCrypt
{
    const LETTERS = 1;    //0001
    const NUMBERS = 2;    //0010
    const ALPHA_NUM = 3;    //0011
    const URL_CHARACTERS = 4;   //0100
    const FOR_URL = 7;    //0111
    const SPECIAL_CHARACTERS = 8; //1000
    //Konstanten f�r die Verschl�sselung
    const MD5 = '1';
    const BLOWFISH_OLD = '2a';
    const BLOWFISH = '2y';
    const BLOWFISH_FALSE = '2x';
    const SHA256 = '5';
    const SHA512 = '6';

    private $hashAlgorithm = self::SHA256;

    /**
     * PwCrypt::checkHashStrength wird immer false zur�ckliefern, wenn dieser Wert true ist
     *
     * @var boolean
     */
    private $dontCheckHashStrength = false;

    /**
     * @param string $lvl Gibt den zu verwendenden Hashalgorithmus an (Klassenkonstante)
     */
    public function __construct($lvl = '')
    {
        if (!empty($lvl)) {
            $this->hashAlgorithm = $lvl;
        }

        // wenn 2x oder 2y gew�hlt, aber nicht verf�gbar, nutze 2a
        if (version_compare(PHP_VERSION, '5.3.7', '<')
            && in_array($this->hashAlgorithm, array(self::BLOWFISH, self::BLOWFISH_FALSE))
        ) {
            $this->hashAlgorithm = self::BLOWFISH_OLD;
        }

        // Pr�fen welche Hash Funktionen Verf�gbar sind. Ab 5.3.2 werden alle mitgeliefert
        if (version_compare(PHP_VERSION, '5.3.2', '<')) {
            if ($this->hashAlgorithm === self::SHA512 && (!defined('CRYPT_SHA512') || CRYPT_SHA512 !== 1)) {
                $this->hashAlgoriathm = self::SHA256; // Wenn SHA512 nicht verf�gbar, versuche SHA256
            }
            if ($this->hashAlgorithm === self::SHA256 && (!defined('CRYPT_SHA256') || CRYPT_SHA256 !== 1)) {
                $this->hashAlgorithm = self::BLOWFISH_OLD; // Wenn SHA256 nicht verf�gbar, versuche BLOWFISH
            }
            if ($this->hashAlgorithm === self::BLOWFISH_OLD && (!defined('CRYPT_BLOWFISH') || CRYPT_BLOWFISH !== 1)) {
                $this->hashAlgorithm = self::MD5; // Wenn BLOWFISH nicht verf�gbar, nutze MD5
            }
        }

        /* Wenn 2a oder 2x gew�hlt, aber 2y verf�gbar: nutze trotzdem 2y, da dies sicherer ist; */
        if (version_compare(PHP_VERSION, '5.3.7', '>=')
            && in_array($this->hashAlgorithm, array(self::BLOWFISH_OLD, self::BLOWFISH_FALSE))
        ) {
            $this->hashAlgorithm = self::BLOWFISH;
        }
    }

    /**
     * Erstellt eine zuf�llige Zeichenkette
     *
     * @param integer $size L�nge der Zeichenkette
     * @param integer $chars Angabe welche Zeichen f�r die Zeichenkette verwendet werden
     * @return string
     */
    public static function getRndString($size = 20, $chars = self::LETTERS)
    {
        if ($chars & self::LETTERS) {
            $pool = 'abcdefghijklmnopqrstuvwxyz';
            $pool .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if ($chars & self::NUMBERS) {
            $pool .='0123456789';
        }

        //in einer URL nicht reservierte Zeichen
        if ($chars & (self::URL_CHARACTERS | self::SPECIAL_CHARACTERS)) {
            $pool .= '-_.~';
        }

        //restiliche Sonderzeichen
        if ($chars & self::SPECIAL_CHARACTERS) {
            $pool .= '!#$%&()*+,/:;=?@[]';
        }

        $pool = str_shuffle($pool);
        $pool_size = strlen($pool);
        $string = '';
        for ($i = 0; $i < $size; $i++) {
            //TODO: Zufallszahlen aus /dev/random bzw /dev/urandom wenn verf�gbar
            $string .= $pool[mt_rand(0, $pool_size - 1)];
        }
        return $string;
    }

    /**
     * Pr�ft, ob der �bergebene Hash, im crypt Format ist
     *
     * @param mixed $hash
     * @return boolean
     */
    public static function isCryptHash($hash)
    {
        return (preg_match('/^\$([156]|2[axy])\$/', $hash) === 1);
    }

    /**
     * Wenn der �bergebene Hash einen schw�cheren Algorithmus verwendet (kleinere Zahl) wird true zur�ck geliefert
     * (schw�chere Hashs werden an andere Stelle (user_pw_check()) mit neuem Algorithmus gespeichert)
     * 
     * @param string $hash
     * @return boolean
     */
    public function checkHashStrength($hash)
    {
        $matches = array();
        if ($this->dontCheckHashStrength) {
            return false;
        }
        if (!self::isCryptHash($hash)) {
            return true;
        }
        if (preg_match('/^\$([1256])([axy])?\$/', $hash, $matches) === 1) {
            $hashAlgoNumber = $matches[1];
            $hashAlgoLetter = isset($matches[2]) ? $matches[2] : '';
            if (preg_match('/^([1256])([axy])?$/', $this->hashAlgorithm, $matches) === 1) {
                if ($matches[1] > $hashAlgoNumber) {
                    return true;
                } elseif ($matches[1] === '2' && $hashAlgoNumber === '2' && $matches[2] > $hashAlgoLetter) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Gibt den Code der gew�hlten/genutzen Hashmethode zur�ck (Crypt Konstante)
     *
     * @return string
     */
    public function getHashAlgorithm()
    {
        return $this->hashAlgorithm;
    }

    /**
     * Erstellt ein Hash f�r das �bergebene Passwort
     *
     * @param string $passwd Klartextpasswort
     * @param string $salt Salt f�r den Hashalgorithus
     * @param integer $rounds Anzahl der Runden f�r den verwendeten Hashalgorithmus
     * @return string Hash des Passwortes (Ausgabe von crypt())
     */
    public function cryptPasswd($passwd, $salt = '', $rounds = 0)
    {
        $salt_string = '';
        switch ($this->hashAlgorithm) {
            case self::SHA512:
            case self::SHA256:
                $salt = (empty($salt) ? self::getRndString(16, self::LETTERS | self::NUMBERS) : $salt);
                if ($rounds < 1000 || $rounds > 999999999) {
                    $rounds = mt_rand(2000, 10000);
                }
                $salt_string = '$' . $this->hashAlgorithm . '$rounds=' . $rounds . '$' . $salt . '$';
                break;
            case self::BLOWFISH:
            case self::BLOWFISH_OLD:
                $salt = (empty($salt) ? self::getRndString(22, self::LETTERS | self::NUMBERS) : $salt);
                if ($rounds < 4 || $rounds > 31) {
                    $rounds = mt_rand(6, 10);
                }
                $salt_string = '$' . $this->hashAlgorithm . '$' . str_pad($rounds, 2, '0', STR_PAD_LEFT) . '$' . $salt . '$';
                break;
            case self::MD5:
                $salt = (empty($salt) ? self::getRndString(12, self::LETTERS | self::NUMBERS) : $salt);
                $salt_string = '$' . $this->hashAlgorithm . '$' . $salt . '$';
                break;
            default:
                return false;
        }
        $crypted_pw = crypt($passwd, $salt_string);
        if (strlen($crypted_pw) < 13) {
            return false;
        }
        return $crypted_pw;
    }

    /**
     * Pr�ft, ob das Klartextpasswort dem Hash "entspricht"
     *
     * @param mixed $passwd Klartextpasswort
     * @param mixed $crypted_passwd Hash des Passwortes (aus der Datenbank)
     * @param boolean $backup wenn Check fehlschl�gt und das alte passwort mit BLOWFISH_OLD verschl�sselt wurde,
     *      werden beide Varianten noch einmal explizit gepr�ft, wenn verf�gbar. Nur nach Transfer der Datenbank verwenden,
     *      da dies ein Sicherheitsrisiko darstellen kann
     * @return boolean
     */
    public function checkPasswd($passwd, $crypted_passwd, $backup = false)
    {
        if (empty($crypted_passwd)) {
            return false;
        }
        if (self::isCryptHash($crypted_passwd)) {
            $new_chrypt_pw = crypt($passwd, $crypted_passwd);
            if (strlen($new_chrypt_pw) < 13) {
                return false;
            }
        } else {
            $new_chrypt_pw = md5($passwd);
        }
        if ($new_chrypt_pw == $crypted_passwd) {
            return true;
        } else {
            if ($backup == true
                && version_compare(PHP_VERSION, '5.3.7', '>=')
                && substr($crypted_passwd, 0, 4) == '$2a$'
            ) {
                $password_x = '$2x$' . substr($crypted_passwd, 4);
                $password_y = '$2y$' . substr($crypted_passwd, 4);
                $password_neu_x = crypt($passwd, $password_x);
                $password_neu_y = crypt($passwd, $password_y);
                if ($password_neu_x === $password_x || $password_neu_y === $password_y) {
                    return true;
                }
            }
        }
        return false;
    }
}
