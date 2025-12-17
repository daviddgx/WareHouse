<?php


// This refers to the previous code block.
require "Crypter.php";

// Do this once then store it somehow:
$key1 = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
$key2 = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
$message = 'B01-A01-P01-N1';

$ciphertext = safeEncrypt($message, $key1);
$plaintext = safeDecrypt($ciphertext, $key2);

var_dump($ciphertext);
var_dump($plaintext);

echo $ciphertext;
echo $plaintext;

?>

