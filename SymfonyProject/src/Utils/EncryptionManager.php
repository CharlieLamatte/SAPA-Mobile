<?php
/**
 * This class requires $_ENV['KEY'] to be defined
 */

namespace App\Utils;

class EncryptionManager
{
    // Constants for string encryption

    private const CIPHER_ALGO = "aes-256-gcm";

    // Constants for file encryption

    private const FILE_ENCRYPTION_BLOCKS = 10000;
    private const FILE_ENCRYPTION_CIPHER_ALGO = "aes-256-cbc";

    public function __construct()
    {
    }

    /**
     * Encrypt data
     *
     * @param $data string
     * @return false|string
     */
    public static function encrypt($data)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER_ALGO));
        if (!$iv) {
            return false;
        }

        $encrypted_data = openssl_encrypt(
            $data,
            self::CIPHER_ALGO,
            hex2bin($_ENV['KEY']),
            0,
            $iv,
            $tag
        );
        if (!$encrypted_data) {
            return false;
        }

        return $encrypted_data . ":" . base64_encode($iv) . ":" . base64_encode($tag);
    }

    /**
     * Decrypt data that was encrypted using EncryptionManager::encrypt()
     *
     * @param $encrypted
     * @return false|string
     */
    public static function decrypt($encrypted)
    {
        $parts = explode(':', $encrypted);

        return openssl_decrypt(
            $parts[0],
            self::CIPHER_ALGO,
            hex2bin($_ENV['KEY']),
            0,
            base64_decode($parts[1]),
            base64_decode($parts[2]),
        );
    }

    /**
     * Generate a 32 bytes key (in hex) using a Cryptographically Secure Pseudo-Random Number Generator (CSPRNG)
     *
     * @return false|string
     */
    public static function generate_key()
    {
        $key = openssl_random_pseudo_bytes(32, $strong_result);
        if (!$key || !$strong_result) {
            return false;
        }

        return bin2hex($key);
    }

    /**
     * @param  $source string  Path of the unencrypted file
     * @param  $dest string  Path of the encrypted file to create
     */
    public static function encrypt_file(string $source, string $dest)
    {
        $iv_length = openssl_cipher_iv_length(self::FILE_ENCRYPTION_CIPHER_ALGO);
        $iv = openssl_random_pseudo_bytes($iv_length);

        $filepointer_source = fopen($source, 'rb');
        $filepointer_dest = fopen($dest, 'w');

        fwrite($filepointer_dest, $iv);

        while (!feof($filepointer_source)) {
            $plaintext = fread($filepointer_source, $iv_length * self::FILE_ENCRYPTION_BLOCKS);
            $ciphertext = openssl_encrypt(
                $plaintext,
                self::FILE_ENCRYPTION_CIPHER_ALGO,
                hex2bin($_ENV['KEY']),
                OPENSSL_RAW_DATA,
                $iv
            );
            $iv = substr($ciphertext, 0, $iv_length);

            fwrite($filepointer_dest, $ciphertext);
        }

        fclose($filepointer_source);
        fclose($filepointer_dest);
    }


    /**
     * @param  $source string  Path of the encrypted file
     * @param  $dest string  Path of the unencrypted file to create
     */
    public static function decrypt_file(string $source, string $dest)
    {
        $iv_length = openssl_cipher_iv_length(self::FILE_ENCRYPTION_CIPHER_ALGO);

        $filepointer_source = fopen($source, 'rb');
        $filepointer_dest = fopen($dest, 'w');

        $iv = fread($filepointer_source, $iv_length);

        while (!feof($filepointer_source)) {
            $ciphertext = fread($filepointer_source, $iv_length * (self::FILE_ENCRYPTION_BLOCKS + 1));
            $plaintext = openssl_decrypt(
                $ciphertext,
                self::FILE_ENCRYPTION_CIPHER_ALGO,
                hex2bin($_ENV['KEY']),
                OPENSSL_RAW_DATA,
                $iv
            );
            $iv = substr($ciphertext, 0, $iv_length);

            fwrite($filepointer_dest, $plaintext);
        }

        fclose($filepointer_source);
        fclose($filepointer_dest);
    }
}