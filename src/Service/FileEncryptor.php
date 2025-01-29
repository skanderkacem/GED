<?php

namespace App\Service;

use SodiumException;

class FileEncryptor
{
    private $key;

    public function __construct(string $key)
    {
        $this->key  = sodium_crypto_generichash($key, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

    }

    public function encryptFile(string $inputFile)
    {
        try {
            $input = file_get_contents($inputFile);
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $cipherText = sodium_crypto_secretbox($input, $nonce, $this->key);
            $output = $nonce . $cipherText;
            file_put_contents($inputFile, $output);
            return 'success';
        } catch (SodiumException $e) {
            throw new \Exception('Encryption failed: ' . $e->getMessage());
         
        }
    }

    public function decryptFile(string $inputFile)
    {
        try {
            $input = file_get_contents($inputFile);
            $nonce = mb_substr($input, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
            $cipherText = mb_substr($input, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
            $output = sodium_crypto_secretbox_open($cipherText, $nonce, $this->key);
            
            return $output;

        } catch (SodiumException $e) {
            throw new \Exception('Decryption failed: ' . $e->getMessage());
        }
    }
}
