

// Cryptography handling library for client side operations.


function checkCrypto() {
	return window.crypto && crypto.subtle && window.TextEncoder;
}

result = checkCrypto();
if (!result) {
	Window.alert("Crypto is not supported.");
} else {
	window.alert("Crypto is supported.");
}