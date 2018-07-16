<?php
namespace Laravelladder\Core\Utils\String;

/**
 * Class Crypt
 *
 * 加密，解密相关的通用方法
 * @package Laravelladder\Core\Utils\String
 */
class Crypt
{
	private static $OPENSSL_CIPHER_NAME = "aes-128-cbc"; //Name of OpenSSL Cipher
	private static $CIPHER_KEY_LEN = 16; //128 bits
	
	/**
	 * Encrypt data using AES Cipher (CBC) with 128 bit key
	 *
	 * @param string $key - key to use should be 16 bytes long (128 bits)
	 * @param string $data - data to encrypt
	 * @return encrypted data in base64 encoding with iv attached at end after a :
	 */
	
	static function encrypt($key, $data) {
		$ivlen = openssl_cipher_iv_length(static::$OPENSSL_CIPHER_NAME);
		$iv = openssl_random_pseudo_bytes($ivlen);
		if (strlen($key) < static::$CIPHER_KEY_LEN) {
			$key = str_pad("$key", static::$CIPHER_KEY_LEN, "0"); //0 pad to len 16
		} else if (strlen($key) > static::$CIPHER_KEY_LEN) {
			$key = substr("$key", 0, static::$CIPHER_KEY_LEN); //truncate to 16 bytes
		}
		
		$encodedEncryptedData = base64_encode(openssl_encrypt($data, static::$OPENSSL_CIPHER_NAME, $key, OPENSSL_RAW_DATA, $iv));
		$encodedIV = base64_encode($iv);
		$encryptedPayload = $encodedEncryptedData.":".$encodedIV;
		
		return $encryptedPayload;
		
	}
	
	/**
	 * Decrypt data using AES Cipher (CBC) with 128 bit key
	 *
	 * @param string $key - key to use should be 16 bytes long (128 bits)
	 * @param string $data - data to be decrypted in base64 encoding with iv attached at the end after a :
	 * @return decrypted data
	 */
	static function decrypt($key, $data) {
		if (strlen($key) < static::$CIPHER_KEY_LEN) {
			$key = str_pad("$key", static::$CIPHER_KEY_LEN, "0"); //0 pad to len 16
		} else if (strlen($key) > static::$CIPHER_KEY_LEN) {
			$key = substr("$key", 0, static::$CIPHER_KEY_LEN); //truncate to 16 bytes
		}
		
		$parts = explode(':', $data); //Separate Encrypted data from iv.
		$decryptedData = openssl_decrypt(base64_decode($parts[0]), static::$OPENSSL_CIPHER_NAME, $key, OPENSSL_RAW_DATA, base64_decode($parts[1]));
		
		return $decryptedData;
	}
}
