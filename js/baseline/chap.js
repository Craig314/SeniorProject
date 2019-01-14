/*

SEA-CORE International Ltd.
SEA-CORE Development Group

CHAP API JavaScript File

Handles the Challenge Authentication Protocol for password
encryption before it is sent to the server.

*/

var chapAPI= ({

	// Returns true if cryptographic functions are available.
	// False if not.
	checkCrypto: function() {
		if (typeof crypto.subtle === 'object') return true;
		return false;
	},

	// Calculates a CHAP result from the given parameters.
	calcCHAPResponse: async function(passwd, hash, salt, count, challenge) {
		var binSalt;
		var binChallenge;
		var longpass;
		var loopcount;
		var pwdhash;
		var temphash;
		var merged;
		var i;

		// Convert given data into binary
		binSalt = this.hex2bin(salt);
		binChallenge = this.hex2bin(challenge);

		// Process password
		longpass = '';
		loopcount = 1024 / passwd.length;
		if (Math.round(loopcount) > loopcount)
			loopcount--;
		if (loopcount > 0) {
			for (i = 0; i < loopcount; i++)
				longpass += passwd;
		} else {
			longpass = passwd;
		}

		// Run the hash
		temphash = this.hashstr(hash, longpass);
		pwdhash = await temphash;
		for (i = 0; i < count; i++) {
			merged = this.mergeTypedArray(pwdhash, binSalt);
			temphash = this.hashdata(hash, merged);
			pwdhash = await temphash;
		}

		// Compute the final result.
		if (challenge != null) {
			temphash = this.hashdata(hash, this.mergeTypedArray(pwdhash, binChallenge));
			pwdhash = await temphash;
		}

		// Return result
		return this.bin2hex(pwdhash);
	},

	// Converts a hex string into binary data.
	hex2bin: function(hex) {
		if (hex == null) return null;
		return new Uint8Array(hex.match(/[\da-f]{2}/gi).map(function (h) {
			return parseInt(h, 16)}));
	},

	// Converts binary data to a hex string.
	bin2hex: function(bin) {
		return [].map.call(new Uint8Array(bin), b => ('00' + b.toString(16)).slice(-2)).join('');
	},

	// Hashes binary data.
	hashdata: async function(hash, data) {
		var digest;
		var array;

		digest = await crypto.subtle.digest(hash, data);
		array = new Uint8Array(digest);
		return array;
	},

	// Hashes a string.
	hashstr: async function(hash, str) {
		var encoder;
		var data;
		var digest;
		var array;

		encoder = new TextEncoder();
		data = encoder.encode(str);
		digest = await crypto.subtle.digest(hash, data);
		array = new Uint8Array(digest);
		return array;
	},

	// Merges two typed arrays together.
	mergeTypedArray: function(a, b) {
		var c;

		c = new Int8Array(a.length + b.length);
		c.set(a);
		c.set(b, a.length);
		return c;
	},

	// Converts the hash name to something that we can use.
	// Basically, it inserts a dash in the name if needed.
	// Otherwise, it returns the parameter unchanged.
	convertDigest: function(hash) {
		var ucase;

		ucase = hash.toUpperCase();
		if (ucase === 'SHA256') return 'SHA-256';
		if (ucase === 'SHA384') return 'SHA-384';
		if (ucase === 'SHA512') return 'SHA-512';
		return hash;
	},

	// Checks if the given hash function is supported.
	checkDigest: function(hash) {
		var ucase;

		ucase = hash.toUpperCase();
		if (ucase === 'SHA-256') return true;
		if (ucase === 'SHA-384') return true;
		if (ucase === 'SHA-512') return true;
		return false;
	},

});

