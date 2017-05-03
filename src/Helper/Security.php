<?php
namespace Helper;

/**
 * Операции для обеспечения безопасности
 * 
 * @uses \Sys\Helper
 * @uses \Sys\Helper\Str
 */
class Security
{
    /**
     * Формирует заданное число случайных байтов.
     * Обратите внимание, что выход не может быть ASCII.
     * @see TokenCreate() если вам нужна строка со случайными символами "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz", "0123456789".
     *
     * @param integer $length Число байтов для генерации
     * @return string Сгенерированные случайные байты
     * @throws Exception В случае неудачи.
     */
    public function RandomKey($length = 32) {
        if (!is_int($length)) {
            throw new \Exception('First parameter ($length) must be an integer');
        }
        if ($length < 1) {
            throw new \Exception('First parameter ($length) must be greater than 0');
        }
        // always use random_bytes() if it is available
        if (function_exists('random_bytes')) {
            return random_bytes($length);
        }
        // mcrypt_create_iv() does not use libmcrypt. Since PHP 5.3.7 it directly reads
        // CryptGenRandom on Windows. Elsewhere it directly reads /dev/urandom.
        if (function_exists('mcrypt_create_iv')) {
            $key = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            if (\Sys\Helper::$String->ByteLength($key) === $length) {
                return $key;
            }
        }
        if(function_exists('mt_rand')){
            $string = '0123456789ABCDEF';	
            $max = strlen($string) - 1;	
            $key = '';	
            for ($i = 0; $i < $length*2; $i++) {
                $key .= $string[mt_rand(0, $max)];
            }
            return pack('H*', $key);
        }

        throw new Exception('Unable to generate a random key');
    }
    /**
     * Создать случайный маркер из символов [A-Z,a-z,0-9]
     * 
     * @param int $length Длинна создаваемого маркера
     * @return string
     */
    public function TokenCreate($length = 32) {
	$string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';	
	$max = mb_strlen($string) - 1;	
	$token = '';	
	for ($i = 0; $i < $length; $i++) {
            $token .= mb_substr($string, mt_rand(0, $max), 1);
	}
	return $token;
    }
    /**
     * Создает безопасный хэш из пароля и случайной строки salt.
     *
     * @param string $password Пароль для хэширования.
     * @param integer $cost Параметр $cost используется хэш-алгоритма Blowfish.
     * Чем выше значение $cost, тем больше времени требуется для создания хэш 
     * и проверки пароля против него. Поэтому более высокая $cost замедляет 
     * атаки грубой силы. Для лучшей защиты от грубой силы нападения, установить 
     * его на самом высоком значении, что является допустимым на производственных 
     * серверах.
     * 
     * @throws Exception нарушайщие правила параметры $password или $cost.
     * @see ValidatePassword()
     */
    public function PasswordHash($password, $cost = 13) {
        if (function_exists('password_hash')) {
            /** @noinspection PhpUndefinedConstantInspection */
            return password_hash($password, PASSWORD_DEFAULT, ['cost' => $cost]);
        }
        $salt = $this->Salt($cost);
        $hash = crypt($password, $salt);
        // strlen() is safe since crypt() returns only ascii
        if (!is_string($hash) || strlen($hash) !== 60) {
            throw new \Exception('Unknown error occurred while generating password hash.'); exit();
        }
        return $hash;
    }
    /**
     * Проверяется пароль против хэш.
     * 
     * @param string $password Пароль для проверки.
     * @param string $hash Хэш для проверки пароля против.
     * @return boolean правильный ли пароль.
     * @throws нарушайщие правила параметры $password или $cost.
     * @see PasswordHash()
     */
    public function ValidatePassword($password, $hash) {
        if (!is_string($password) || $password === '') {
            throw new \Exception('Password must be a string and cannot be empty.');
        }

        if (!preg_match('/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', $hash, $matches)
            || $matches[1] < 4
            || $matches[1] > 30
        ) {
            throw new \Exception('Hash is invalid.');
        }

        if (function_exists('password_verify')) {
            return password_verify($password, $hash);
        }
        $test = crypt($password, $hash);
        $n = strlen($test);
        if ($n !== 60) {
            return false;
        }
        return \Sys\Helper::$String->CompareString($test, $hash);
    }
    /**
     * Формирует salt, которую можно использовать для генерации хэш пароля.
     *
     * PHP [crypt()] (http://php.net/manual/en/function.crypt.php) встроенная функция
     * требуется для хэш-алгоритма Blowfish, salt строки в определенном формате:
     * "$2a$", "$2x$" или "$2y$", параметр cost две цифры, "$", а 22 символы
     * из алфавита "./0-9A-Za-z".
     *
     * @param integer $cost the cost parameter
     * @return string Случайная величина salt.
     * @throws Exception Если параметр $cost находится вне диапазона от 4 до 31.
     */
    public function Salt($cost = 13) {
        $cost = (int)$cost;
        if ($cost < 4 || $cost > 31) {
            throw new \Exception('Cost must be between 4 and 31.'); exit();
        }

        // Get a 20-byte random string
        $rand = $this->RandomKey(20);
        // Form the prefix that specifies Blowfish (bcrypt) algorithm and cost parameter.
        $salt = sprintf("$2y$%02d$", $cost);
        // Append the random salt data in the required base64 format.
        $salt .= str_replace('+', '.', substr(base64_encode($rand), 0, 22));

        return $salt;
    }
    /**
     * Кодирование данных для HTML атрибутов
     * 
     * @param mixed $data Данные в чистом виде
     * @param string $encoding Тип кодировки данных
     * 
     * @return mixed Закодированные для HTML атрибутов данные
     */
    public function EncodeHtml($data, $encoding = 'UTF-8') {
        $result;
        if (is_array($data)) {
            $result=[];
            foreach ($data as $key => $value) {
                unset($data[$key]);
                $result[$this->EncodeHtml($key, $encoding)] = $this->EncodeHtml($value, $encoding);
            }
        } else {
            $result = htmlspecialchars($data, ENT_COMPAT, $encoding);
        }
        return $result;
    }
    /**
     * Разкодирование данных из HTML
     *  
     * @param mixed $data Закодированные для HTML данные
     * @param string $encoding Тип кодировки данных
     * 
     * @return mixed Данные в чистом виде
     */
    public function DecodeHtml($data, $encoding = 'UTF-8'){
        $result;
        if (is_array($data)) {
            $result=[];
            foreach ($data as $key => $value) {
                unset($data[$key]);
                $result[$this->DecodeHtml($key, $encoding)] = $this->DecodeHtml($value, $encoding);
            }
        } else {
            $result = html_entity_decode($data, ENT_QUOTES, $encoding);
        }
        return $result;
    }
    /**
     * Кодирование ссылки
     * 
     * @param string $url Чистая строка ссылки
     * @return string Закодированная строка ссылки
     */
    public function EncodeUrl($url){
        return urlencode($str);
    }
    /**
     * Разкодирование ссылки
     * 
     * @param string $url Закодированная строка ссылки
     * @return string Чистая строка ссылки
     */
    public function DecodeUrl($url){
        return urldecode($str);
    }
    /**
     * Кодирование данных для SQL запросов
     *  
     * @param mixed $data Данные в чистом виде
     * @return mixed Закодированные для SQL запросов
     */
    public function EncodeSql($data){
        $result;
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                unset($data[$key]);
                $result[$this->EncodeSql($key)] = $this->EncodeSql($value);
            }
        } else {
            $result = str_replace(array("\\", "\0", "\n", "\r", "\x1a", "'", '"'), array("\\\\", "\\0", "\\n", "\\r", "\Z", "\'", '\"'), $data);
        }
        return $result;
    }
    /**
     * Кодирование данных в формат JSON
     *  
     * @param mixed $data Данные в чистом виде
     * @return string Строка JSON
     */
    public function EncodeJson($data){
        if(function_exists('json_encode')) json_encode($data);
        else {
            switch (gettype($data)) {
                case 'boolean':
                    return $data ? 'true' : 'false';
                case 'integer':
                case 'double':
                    return $data;
                case 'resource':
                case 'string':
                    # Escape non-printable or Non-ASCII characters.
                    # I also put the \\ character first, as suggested in comments on the 'addclashes' page.
                    $json = '';
                    $string = '"' . addcslashes($data, "\\\"\n\r\t/" . chr(8) . chr(12)) . '"';
                    # Convert UTF-8 to Hexadecimal Codepoints.
                    for ($i = 0; $i < strlen($string); $i++) {
                        $char = $string[$i];
                        $c1 = ord($char);
                        # Single byte;
                        if ($c1 < 128) {
                            $json .= ($c1 > 31) ? $char : sprintf("\\u%04x", $c1);
                            continue;
                        }
                        # Double byte
                        $c2 = ord($string[++$i]);
                        if (($c1 & 32) === 0) {
                            $json .= sprintf("\\u%04x", ($c1 - 192) * 64 + $c2 - 128);
                            continue;
                        }
                        # Triple
                        $c3 = ord($string[++$i]);
                        if (($c1 & 16) === 0) {
                            $json .= sprintf("\\u%04x", (($c1 - 224) <<12) + (($c2 - 128) << 6) + ($c3 - 128));
                            continue;
                        }
                        # Quadruple
                        $c4 = ord($string[++$i]);
                        if (($c1 & 8 ) === 0) {
                            $u = (($c1 & 15) << 2) + (($c2 >> 4) & 3) - 1;
                            $w1 = (54 << 10) + ($u << 6) + (($c2 & 15) << 2) + (($c3 >> 4) & 3);
                            $w2 = (55 << 10) + (($c3 & 15) << 6) + ($c4 - 128);
                            $json .= sprintf("\\u%04x\\u%04x", $w1, $w2);
                        }
                    }
                    return $json;
                case 'array':
                    if (empty($data) || array_keys($data) === range(0, sizeof($data) - 1)) {
                        $output = array();
                        foreach ($data as $value) {
                            $output[] = json_encode($value);
                        }
                        return '[' . implode(',', $output) . ']';
                    }
                case 'object':
                    $output = array();
                    foreach ($data as $key => $value) {
                        $output[] = json_encode(strval($key)) . ':' . json_encode($value);
                    }
                    return '{' . implode(',', $output) . '}';
                default:
                        return 'null';
            }
        }
    }
    /**
     * Разкодирование данных из формата JSON
     *  
     * @param string $data Строка JSON
     * @param boolean $assoc Формат получаемых данных, если TRUE то array, в противном случае object
     * @return mixed Данные в чистом виде
     */
    public function DecodeJson($json, $assoc = false) {
        if(function_exists('json_decode')) return json_decode($json, $assoc);
        else {
            $match = '/".*?(?<!\\\\)"/';
            $string = preg_replace($match, '', $json);
            $string = preg_replace('/[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/', '', $string);
            if ($string != '') {
                return null;
            }
            $s2m = array();
            $m2s = array();
            preg_match_all($match, $json, $m);
            foreach ($m[0] as $s) {
                $hash = '"' . md5($s) . '"';
                $s2m[$s] = $hash;
                $m2s[$hash] = str_replace('$', '\$', $s);
            }
            $json = strtr($json, $s2m);
            $a = ($assoc) ? '' : '(object) ';
            $data = array(
                ':' => '=>',
                '[' => 'array(',
                '{' => "{$a}array(",
                ']' => ')',
                '}' => ')'
            );
            $json = strtr($json, $data);
            $json = preg_replace('~([\s\(,>])(-?)0~', '$1$2', $json);
            $json = strtr($json, array_map('stripcslashes', $m2s));
            $function = @create_function('', "return {$json};");
            $return = ($function) ? $function() : null;
            unset($s2m);
            unset($m2s);
            unset($function);
            return $return;
        }
    }
    /**
     * Кодирование данных в формат Base64
     * 
     * @param string $data Строка в чистом виде
     * @return string Строка Base64
     */
    public function EncodeBase64($data){
        return base64_encode($data);
    }
    /**
     * Разкодирование данных из формата Base64
     * 
     * @param string Строка Base64
     * @return Строка в чистом виде
     */
    public function DecodeBase64($base64){
        return base64_decode($base64);
    }
}